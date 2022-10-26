<?php

namespace PromCMS\Core\Http\Routes;

use DI\Container;
use Slim\Routing\RouteCollectorProxy as Router;

interface CoreRoutes
{
  public function __construct(Container $container);
  public function attachAllHandlers(Router $router);
}
