<?php

namespace PromCMS\Core\Http\Routing;

use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteCollectorProxy as Router;

interface RouteImplementation
{
  public function attach(Router &$router, callable|string $callable): RouteInterface;

  public function setRoutePrefix(string $prefix): static;
}