<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\AsRouteGroup;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Utils\HttpUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
// TODO - prom__user_roles is only valid
#[AsRouteGroup('/entry-types/{modelId:user-roles|userRoles|prom__user_roles}')]
class UserRolesController
{
  private PromConfig $promConfig;

  public function __construct(Container $container)
  {
    $this->promConfig = $container->get(PromConfig::class);
  }

  #[
    AsApiRoute('GET', '/items'),
    WithMiddleware(UserLoggedInMiddleware::class),
  ]
  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    return ResponseHelper::withServerResponse($response, array_map(fn($entry) => $entry->__toArray(), $this->promConfig->getProject()->security->roles->getRoles()))->getResponse();
  }

  #[
    AsApiRoute('GET', '/items/{itemId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
  ]
  public function getOne(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $itemId = $request->getAttribute('itemId');
    $item = $this->promConfig->getProject()->security->roles->getRoleBySlug($itemId);

    try {
      if (!$item) {
        throw new EntityNotFoundException();
      }

      HttpUtils::prepareJsonResponse(
        $response,
        $item->__toArray(),
      );

      return $response;
    } catch (\Exception $e) {
      return $response->withStatus(404);
    }
  }
}
