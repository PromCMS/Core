<?php

namespace PromCMS\Core\Http\Routing;

use Attribute;
use Psr\Http\Message\UriInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteCollectorProxy as Router;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class AsRedirectRoute implements RouteImplementation
{
  private string $routePrefix = "";

  public function __construct(
    public readonly string $from,
    public readonly UriInterface|string $to,
    public readonly ?int $status = 302,
    public readonly ?string $name = null
  ) {
  }

  public function attach(Router &$router, callable|string $callable): RouteInterface
  {
    $responseFactory = $router->getResponseFactory();

    $handler = function () use ($responseFactory) {
      $response = $responseFactory->createResponse($this->status);

      return $response->withHeader('Location', (string) $this->to);
    };

    $route = $router->get($this->routePrefix . $this->from, $handler);

    if ($this->name) {
      $route->setName($this->name);
    }

    return $route;
  }

  public function setRoutePrefix(string $prefix): static
  {
    $this->routePrefix = $prefix;

    return $this;
  }
}