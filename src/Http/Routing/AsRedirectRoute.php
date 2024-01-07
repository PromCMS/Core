<?php

namespace PromCMS\Core\Http\Routing;

use PromCMS\Core\Http\Routing\Interface\Route;
use Psr\Http\Message\UriInterface;
use Slim\Routing\RouteCollectorProxy as Router;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[\Attribute(Attribute::TARGET_METHOD)]
final class AsRedirectRoute implements Route
{
  public readonly array $methods;

  public function __construct(
    public readonly string $from,
    public readonly UriInterface|string $to,
    public readonly ?int $status = 302,
    public readonly ?string $name = null
  ) {
  }

  public function attach(Router &$router)
  {
    $responseFactory = $router->responseFactory;

    $handler = function () use ($responseFactory) {
      $response = $responseFactory->createResponse($this->status);

      return $response->withHeader('Location', (string) $this->to);
    };

    $route = $router->get($this->from, $handler);

    if ($this->name) {
      $route->setName($this->name);
    }
  }
}