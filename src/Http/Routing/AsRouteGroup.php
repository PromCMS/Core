<?php

namespace PromCMS\Core\Http\Routing;

use Attribute;

/**
 * Useful attribute that you can attach to controller and specify global rules to defined route methods.
 * 
 * `Say goodbye to repetitve definitions`
 * 
 * @Annotation
 * @Target("CLASS")
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AsRouteGroup
{
  public function __construct(
    /**
     * Applies pathname prefixe to route methods, if defined
     * 
     * @example
     * AsRouteGroup(pathnamePrefix: '/users')
     * <class>
     * AsRoute('GET', '/create')
     * <method>
     * 
     * Results in GET route on pathname /users/create
     */
    public readonly ?string $pathnamePrefix = null
  ) {
  }
}