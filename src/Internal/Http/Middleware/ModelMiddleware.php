<?php

namespace PromCMS\Core\Internal\Http\Middleware;

use DI\Container;

class ModelMiddleware extends EntityMiddleware
{
  public function __construct(Container $container)
  {
    parent::__construct($container, EntityMiddlewareMode::MODEL);
  }
}
