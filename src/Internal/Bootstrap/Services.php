<?php

namespace PromCMS\Core\Internal\Bootstrap;

use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\ImageService;
use PromCMS\Core\Services\JWTService;
use PromCMS\Core\Services\LocalizationService;
use PromCMS\Core\Services\RouteCollectorService;
use PromCMS\Core\Services\SchemaService;
use PromCMS\Core\Services\UserService;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class Services implements AppModuleInterface
{
  public function run($app, $container)
  {
    $container->set(JWTService::class, new JWTService($container));
    $container->set(ImageService::class, new ImageService($container));
    $container->set(FileService::class, new FileService($container));
    $container->set(UserService::class, new UserService($container));
    $container->set(
      LocalizationService::class,
      new LocalizationService($container),
    );

    $container->set(RouteCollectorService::class, $app->getRouteCollector());
    $container->set(SchemaService::class, new SchemaService($container));
  }
}
