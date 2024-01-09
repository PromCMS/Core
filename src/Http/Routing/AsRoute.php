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

  public function __construct(string|array $method, protected readonly string $route, protected readonly ?string $name = null)
  {
    $this->methods = is_array($method) ? $method : [$method];
  }

  public function attach(\Slim\Routing\RouteCollectorProxy &$router, callable|string $callable): RouteInterface
  {
    if ($this->methods[0] === 'ANY') {
      $route = $router->any($this->route, $callable);
    } else {
      $route = $router->map($this->methods, $this->route, $callable);
    }

    if ($this->name) {
      $route->setName($this->name);
    }

    return $route;
  }
}