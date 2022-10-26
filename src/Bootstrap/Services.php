<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\ImageService;
use PromCMS\Core\Services\JWTService;
use PromCMS\Core\Services\LocalizationService;
use PromCMS\Core\Services\PasswordService;

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
  }
}
