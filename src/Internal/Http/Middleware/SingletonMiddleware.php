<?php

namespace PromCMS\Core\Internal\Http\Middleware;

use DI\Container;

class SingletonMiddleware extends EntityMiddleware
{
    public function __construct(Container $container)
    {
        parent::__construct($container, EntityMiddlewareMode::SINGLETON);
    }
}
