<?php

namespace PromCMS\Core\Http\Routing\Interface;

use Slim\Routing\RouteCollectorProxy as Router;

interface Route
{
  public function attach(Router $router);
}