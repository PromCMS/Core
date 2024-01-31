<?php

namespace PromCMS\Core\Http\Routing;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class WithMiddleware
{
  public function __construct(public readonly MiddlewareInterface $middleware)
  {
  }
}