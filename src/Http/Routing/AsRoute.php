<?php

namespace PromCMS\Core\Http\Routing;

use PromCMS\Core\Http\Routing\Interface\Route;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[\Attribute(Attribute::TARGET_METHOD)]
final class AsRoute implements Route
{
  public readonly array $methods;

  public function __construct(string|array $method, public readonly string $route, public readonly ?string $name = null)
  {
    $this->methods = is_array($method) ? $method : [$method];
  }

  public function attach(\Slim\Routing\RouteCollectorProxy $router, callable|string $callable)
  {
    if ($this->methods[0] === 'ANY') {
      $route = $router->any($this->route, $callable);
    } else {
      $route = $router->map($this->methods, $this->route, $callable);
    }

    if ($this->name) {
      $route->setName($this->name);
    }
  }
}