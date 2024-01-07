<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Utils\HttpUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class UserRolesController
{
  private PromConfig $promConfig;

  public function __construct(Container $container)
  {
    $this->promConfig = $container->get(PromConfig::class);
  }

  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    return ResponseHelper::withServerResponse($response, array_map(fn($entry) => $entry->__toArray(), $this->promConfig->getProject()->security->roles->getRoles()))->getResponse();
  }

  public function getOne(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $itemId = $args['itemId'];
    $item = $this->promConfig->getProject()->security->roles->getRoleBySlug($itemId);

    if (!$item) {
      throw new EntityNotFoundException();
    }

    try {
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
