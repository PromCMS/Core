<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\ImageService;
use PromCMS\Core\Services\JWTService;
use PromCMS\Core\Services\LocalizationService;
use PromCMS\Core\Services\ModulesService;
use PromCMS\Core\Services\PasswordService;
use PromCMS\Core\Services\RouteCollectorService;
use PromCMS\Core\Services\SchemaService;

class Services implements AppModuleInterface
{
  public function run($app, $container)
  {
    $container->set(PasswordService::class, new PasswordService());
    $container->set(JWTService::class, new JWTService($container));
    $container->set(ImageService::class, new ImageService($container));
    $container->set(FileService::class, new FileService($container));
    $container->set(
      LocalizationService::class,
      new LocalizationService($container),
    );

    $container->set(RouteCollectorService::class, $app->getRouteCollector());
    $container->set(ModulesService::class, new ModulesService($container));
    $container->set(SchemaService::class, new SchemaService($container));
  }
}
