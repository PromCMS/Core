<?php

namespace PromCMS\Core\Http\Middleware;

use PromCMS\Core\Models\User;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Entity;
use PromCMS\Core\PromConfig\Project\Security\RolePermissionOptionKey;
use PromCMS\Core\PromConfig\Project\Security\RolePermissionOptionValue;
use PromCMS\Core\Session;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use PromCMS\Core\Utils\HttpUtils;

class PermissionMiddleware
{
  private Session $session;
  private PromConfig $promConfig;

  public function __construct($container)
  {
    $this->session = $container->get(Session::class);
    $this->promConfig = $container->get(PromConfig::class);
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
    $user = $this->session->get('user', null);
    /**
     * @var Entity
     */
    $entity = $request->getAttribute(Entity::class);

    if (!$user) {
      throw new \Exception('Cannot run permission middleware before auth middleware');
    }

    if (!$entity) {
      throw new \Exception('Cannot run permission middleware before entry type middleware');
    }

    $roleSlug = $user->getRoleSlug();
    $role = $this->promConfig->getProject()->security->roles->getRoleBySlug($roleSlug);
    $permissionByRequestMethod = match ($request->getMethod()) {
      'POST' => RolePermissionOptionKey::CREATE,
      'GET' => RolePermissionOptionKey::READ,
      'HEAD' => RolePermissionOptionKey::READ,
      'PATCH' => RolePermissionOptionKey::UPDATE,
      'DELETE' => RolePermissionOptionKey::DELETE,
      default => throw new \Exception(
        '[permissionMiddleware]: Unexpected request method',
      )
    };
    $rolePermissionOnTable = $role->getPermissionSetForModel($entity->tableName);
    $rolePermissionOnTableValue = $rolePermissionOnTable[$permissionByRequestMethod];

    if (!$role || $rolePermissionOnTableValue === RolePermissionOptionValue::DENY) {
      // TODO: Log this if no role has been found as that may be a missconfig on administrator side

      $response = new Response();

      HttpUtils::prepareJsonResponse(
        $response,
        [],
        'Your user role is not sufficient',
        'role-not-sufficient',
      );

      return $response
        ->withStatus(401)
        ->withHeader('Content-Description', 'Role not sufficient');
    }

    $request = $request->withAttribute(
      'permission-only-own',
      $rolePermissionOnTableValue === RolePermissionOptionValue::ALLOW_OWN
    );

    return $handler->handle($request);
  }
}
