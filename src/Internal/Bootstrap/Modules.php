<?php

namespace PromCMS\Core\Internal\Bootstrap;

use DI\Container;
use PromCMS\Core\Logger;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Services\ModulesService;
use PromCMS\Core\Services\RenderingService;
use PromCMS\Core\Utils\FsUtils;
use Slim\App;
use PromCMS\Core\Module;
use PromCMS\Core\Config;
use Symfony\Component\Filesystem\Path;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class Modules implements AppModuleInterface
{
  public function run(App $app, Container $container)
  {
    $appRoot = $container->get('app.root');
    Module::$modulesRoot = Path::join($appRoot, 'modules');

    $modules = $container->get(ModulesService::class)->getAll();
    $config = $container->get(Config::class);
    $promConfig = $container->get(PromConfig::class);
    $twig = $container->get(RenderingService::class);
    $logger = $container->get(Logger::class);
    $twigFileLoader = $twig->getLoader();

    // Simple autoload load module logic
    foreach ($modules as $module) {
      // Make sure that plugin has valid info file
      $bootstrapFilepath = Path::join($module->getPath(), Module::$bootstrapFileName);
      $bootstrapAfter = Path::join($module->getPath(), Module::$afterBootstrapFileName);
      $apiRoutesFilepath = Path::join($module->getPath(), Module::$apiRoutesFileName);
      $frontRoutesFilepath = Path::join($module->getPath(), Module::$frontRoutesFileName);
      $viewsFolderPath = Path::join($module->getPath(), Module::$viewsFolderName);

      // TODO: add test
      // Load bootstrap for that module
      if (file_exists($bootstrapFilepath)) {
        $bootstrapClosure = require_once $bootstrapFilepath;

        $bootstrapClosure($app);
      }

      // If we have folder of views then we add another view namespace
      if (is_dir($viewsFolderPath)) {
        $twigFileLoader->addPath($viewsFolderPath, 'modules:' . $module->getFolderName());
      }

      // Loads controllers beforehand
      FsUtils::autoloadControllers($module->getPath());

      // TODO: add try catch here
      if (file_exists($bootstrapAfter)) {
        $module = require_once $bootstrapAfter;

        $module($app);
      }
    }
  }
}
