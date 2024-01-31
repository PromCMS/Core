<?php

namespace PromCMS\Core\Http\Routing;

use Attribute;
use Slim\Interfaces\RouteInterface;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AsApiRoute extends AsRoute
{
  public function getRoutePathname()
  {
    return '/api' . parent::getRoutePathname();
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
}