<?php

namespace PromCMS\Core\Http\Middleware;

use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Logger;
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
use Slim\Routing\RouteContext;

class PermissionMiddleware
{
  private Session $session;
  private PromConfig $promConfig;
  private Logger $logger;

  public function __construct($container)
  {
    $this->session = $container->get(Session::class);
    $this->promConfig = $container->get(PromConfig::class);
    $this->logger = $container->get(Logger::class);
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

    if (!$user) {
      throw new \Exception('Cannot run permission middleware before auth middleware');
    }

    $route = RouteContext::fromRequest($request)->getRoute();
    $modelIdParam = $route->getArgument('modelId');

    // For viewing roles you just need to be logged in, managing roles d
    if (in_array($modelIdParam, ['user-roles', 'userRoles', 'prom__user_roles'])) {
      return $handler->handle($request);
    }

    /**
     * @var Entity
     */
    $entity = $request->getAttribute(Entity::class);

    if (!$entity) {
      throw new \Exception('Cannot run permission middleware before entry type middleware');
    }

    $roleSlug = $user->getRoleSlug();
    $role = $this->promConfig->getProject()->security->roles->getRoleBySlug($roleSlug);
    $permissionByRequestMethod = match ($request->getMethod()) {
      'POST' => RolePermissionOptionKey::CREATE->value,
      'GET' => RolePermissionOptionKey::READ->value,
      'HEAD' => RolePermissionOptionKey::READ->value,
      'PATCH' => RolePermissionOptionKey::UPDATE->value,
      'DELETE' => RolePermissionOptionKey::DELETE->value,
      default => throw new \Exception(
        '[permissionMiddleware]: Unexpected request method',
      )
    };
    $rolePermissionOnTable = $role->getPermissionSetForModel($entity->tableName);
    $rolePermissionOnTableValue = $rolePermissionOnTable[$permissionByRequestMethod];

    if (!$role || $rolePermissionOnTableValue === RolePermissionOptionValue::DENY->value) {
      if (!$role) {
        $this->logger->error("User logged in, but role under slug $roleSlug could not be found. Please check your config or change user role", [
          'entity' => $entity->phpName,
          'route' => $route->getPattern(),
          'user' => [
            'id' => $user->getId(),
          ]
        ]);
      }

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
      $rolePermissionOnTableValue === RolePermissionOptionValue::ALLOW_OWN->value
    );

    return $handler->handle($request);
  }
}
