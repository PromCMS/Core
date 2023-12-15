<?php

namespace PromCMS\Core\Http\Middleware;

use PromCMS\Core\Models\Map\FileTableMap;
use PromCMS\Core\Models\Map\UserRoleTableMap;
use PromCMS\Core\Models\Map\UserTableMap;
use PromCMS\Core\Models\UserRoleQuery;
use PromCMS\Core\Models\User;
use PromCMS\Core\Session;
use GuzzleHttp\Psr7\Response;
use Propel\Runtime\Map\TableMap;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use PromCMS\Core\Utils\HttpUtils;

class PermissionMiddleware
{
  private $container;
  private $loadedModels;
  private $adminOnlyModels = [UserTableMap::TABLE_NAME, UserRoleTableMap::TABLE_NAME];
  private array $modelSlugToModelReference = [];

  public function __construct($container)
  {
    $this->container = $container;
    $this->loadedModels = $container->get('sysinfo')['loadedModels'];

    foreach ($this->loadedModels as $loadedModelClassReference) {
      $tableMap = ($loadedModelClassReference)::TABLE_MAP;


      $this->modelSlugToModelReference[$tableMap::TABLE_NAME] = $loadedModelClassReference;
    }
  }

  /**
   * Permission middleware class, it interacts with session and gets if in session theres a sufficient user role for provided route
   *
   * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
   * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
   * @param  callable                                 $next     Next middleware
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
  {
    /**
     * @var User
     */
    $user = $this->container->get(Session::class)->get('user', false);
    $roleId = intval($user->getRoleId());
    $model = $request->getAttribute('model');

    if (!$model) {
      throw new \Exception('Cannot run permission middleware before entry type middleware');
    }


    // TODO we should allow setting permission on files too so it makes sense
    // Handle any other than admin and allow manipulate files on any user
    if ($roleId !== 0) {
      $modelTableName = $model->map::TABLE_NAME;

      if (in_array($modelTableName, $this->adminOnlyModels)) {
        $response = new Response();

        return $response
          ->withStatus(401)
          ->withHeader('Content-Description', 'Role not sufficient');
      }

      $response = new Response();

      $role = UserRoleQuery::create()->findOneById($roleId)->toArray(TableMap::TYPE_CAMELNAME);
      $role = json_decode(json_encode($role), true);
      $modelPermissions = $role['permissions']['models'][$modelTableName];

      $requestMethod = $request->getMethod();
      // 'allow-everything' | 'allow-own' | false
      $requestPermissionValue = false;

      if (isset($modelPermissions)) {
        switch ($requestMethod) {
          case 'POST':
            $requestPermissionValue = $modelPermissions['c'];
            break;
          case 'GET':
            $requestPermissionValue = $modelPermissions['r'];
            break;
          case 'PATCH':
            $requestPermissionValue = $modelPermissions['u'];
            break;
          case 'DELETE':
            $requestPermissionValue = $modelPermissions['d'];
            break;
          default:
            throw new \Exception(
              '[permissionMiddleware]: Unexpected request method',
            );
        }

        $request = $request->withAttribute(
          'permission-only-own',
          $requestPermissionValue === 'allow-own',
        );
      }

      // If there is not yet set permission then we assume that user does not have access to this
      if ($requestPermissionValue === false) {
        HttpUtils::prepareJsonResponse(
          $response,
          [],
          'Your user role is not sufficient',
          'role-not-sufficient',
        );

        return $response
          ->withStatus(401)
          ->withHeader('Content-Description', 'role not sufficient');
      }
    }

    return $handler->handle($request);
  }
}
