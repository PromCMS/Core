<?php

namespace PromCMS\Core\Http\Routing;

use Attribute;
use PromCMS\Core\Http\Routing\RouteImplementation;
use Slim\Interfaces\RouteInterface;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AsRoute implements RouteImplementation
{
  protected readonly array $methods;
  protected string $routePrefix = "";

  public function __construct(string|array $method, protected string $route, protected readonly ?string $name = null)
  {
    $this->methods = is_array($method) ? $method : [$method];
  }

  protected function getRoutePathname()
  {
    return $this->routePrefix . $this->route;
  }

  public function attach(\Slim\Routing\RouteCollectorProxy &$router, callable|string $callable): RouteInterface
  {
    $routePathname = $this->getRoutePathname();

    if ($this->methods[0] === 'ANY') {
      $route = $router->any($routePathname, $callable);
    } else {
      $route = $router->map($this->methods, $routePathname, $callable);
    }

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