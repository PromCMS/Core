<?php

namespace PromCMS\Core\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use GuzzleHttp\Psr7\Response;
use PromCMS\Core\HttpUtils;
use PromCMS\Core\Models\UserRoles;

class PermissionMiddleware
{
  private $container;
  private $loadedModels;
  private $adminOnlyModels;

  public function __construct($container)
  {
    $this->adminOnlyModels = ['users', 'userroles'];
    $this->container = $container;
    $this->loadedModels = $container->get('sysinfo')['loadedModels'];
  }

  /**
   * Checks if model is loaded/exists and returns the real, usable model name
   */
  private function getModelFromArg(string $modelName)
  {
    $modelIndex = array_search(
      strtolower($modelName),
      // format all model names to be all in lowercase
      array_map(function ($modelName) {
        $slicedModelName = explode("\\", $modelName);

        return strtolower(end($slicedModelName));
      }, $this->loadedModels),
    );
    if ($modelIndex === false) {
      return false;
    }

    $modelName = $this->loadedModels[$modelIndex];

    return "\\$modelName";
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
  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $user = $this->container->get('session')->get('user', false);
    $roleId = intval($user->role);
    $requestPath = $request->getUri()->getPath();
    $modelFromUrl = explode('/', $requestPath)[3];
    $modelInstancePath = $this->getModelFromArg($modelFromUrl);

    // Make sure that provided model is there
    if ($modelInstancePath === false) {
      $response = new Response();

      return $response
        ->withStatus(404)
        ->withHeader('Content-Description', 'model does not exist');
    }

    // TODO we should allow setting permission on files too so it makes sense
    // Handle any other than admin and allow manipulate files on any user
    if ($roleId !== 0 && strtolower($modelFromUrl) !== 'files') {
      if (in_array(strtolower($modelFromUrl), $this->adminOnlyModels)) {
        $response = new Response();

        return $response
          ->withStatus(401)
          ->withHeader('Content-Description', 'Role not sufficient');
      }

      $response = new Response();
      $role = UserRoles::getOneById($roleId)->getData();
      $role = json_decode(json_encode($role), true);
      $modelPermissions = $role['permissions']['models'][$modelFromUrl];
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
            break;
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
