<?php

namespace PromCMS\Core\Http\Routing;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[\Attribute(Attribute::TARGET_METHOD)]
final class WithMiddleware
{
  public function __construct(public readonly callable|string $middleware)
  {
  }
}