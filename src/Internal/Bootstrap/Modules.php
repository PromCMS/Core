<?php

namespace PromCMS\Core\Internal\Bootstrap;

use DI\Container;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Database\Models\File;
use PromCMS\Core\Database\Models\GeneralTranslation;
use PromCMS\Core\Database\Models\Setting;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Services\ModulesService;
use PromCMS\Core\Services\RenderingService;
use PromCMS\Core\Utils\FsUtils;
use Slim\App;
use PromCMS\Core\Module;
use PromCMS\Core\Config;
use PromCMS\Core\Http\Routes\ApiRoutes;
use PromCMS\Core\Http\Routes\FrontRoutes;
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
    $twigFileLoader = $twig->getLoader();

    $filePathsToApiRoutes = [];
    $filePathsToFrontRoutes = [];

    // array of loaded model names (names of classes)
    $coreModels = [
      User::class,
      File::class,
      GeneralTranslation::class,
      Setting::class
    ];
    $loadedModels = $coreModels;

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

      // Load models beforehand and save these models to array
      $loadedModuleModels = $module->getDeclaredModels();
      if ($loadedModuleModels) {
        $loadedModels = array_merge($loadedModels, $loadedModuleModels);
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

      // Add api routes definition file to set
      if (file_exists($apiRoutesFilepath)) {
        $filePathsToApiRoutes[] = $apiRoutesFilepath;
      }

      // Add front routes definition file to set
      if (file_exists($frontRoutesFilepath)) {
        $filePathsToFrontRoutes[] = $frontRoutesFilepath;
      }
    }

    // Set some info to memory so modules can access those
    $container->set('sysinfo', [
      'loadedModels' => $loadedModels,
    ]);

    $routePrefix = $promConfig->getProject()->url->getPath();
    $supportedLanguages = $promConfig->getProject()->languages;
    $coreFrontRoutes = new FrontRoutes($container);
    $coreApiRoutes = new ApiRoutes($container);

    // Every module should have been bootstrapped by now so we can continue to including custom routes
    $app->group($routePrefix, function ($router) use ($filePathsToApiRoutes, $filePathsToFrontRoutes, $app, $coreFrontRoutes, $coreApiRoutes, $config) {
      // attach core front routes
      $coreFrontRoutes->attachAllHandlers($router);

      // Load api routes first from prepared set
      $router
        ->group('/api', function ($router) use ($filePathsToApiRoutes, $app, $coreApiRoutes, ) {
          // attach core api routes
          $coreApiRoutes->attachAllHandlers($router);

          foreach ($filePathsToApiRoutes as $filePath) {
            $imported = require_once $filePath;

            if (!is_callable($imported)) {
              throw new \Exception("Route file return at $filePath is not callable");
            }

            $imported($app, $router);
          }
        });

      // Load front routes second - same as api
      foreach ($filePathsToFrontRoutes as $filePath) {
        $imported = require_once $filePath;

        if (!is_callable($imported)) {
          throw new \Exception("Route file return at $filePath is not callable");
        }

        $imported($app, $router);
      }
    })->add(function ($request, $handler) use ($config) {
      $response = $handler->handle($request);

      return $response
        ->withHeader(
          'Access-Control-Allow-Origin',
          $config->env->development ? '*' : '',
        )
        ->withHeader(
          'Access-Control-Allow-Headers',
          'X-Requested-With, Content-Type, Accept, Origin, Authorization',
        )
        ->withHeader(
          'Access-Control-Allow-Methods',
          'GET, POST, DELETE, PATCH',
        );
    });

    $hasPrefix = !empty($routePrefix);
    $intlRoutePrefix =
      $routePrefix . '/{language:' . implode('|', $supportedLanguages) . '}';

    // Attach localized routes on already created routes
    foreach ($app->getRouteCollector()->getRoutes() as $route) {
      $pattern = $route->getPattern();
      // Ignore public files, they are not localized of course
      if (str_starts_with($pattern, "/public")) {
        continue;
      }

      // If has prefix then we remove it
      if ($hasPrefix) {
        $pos = strpos($pattern, $routePrefix);
        if ($pos !== false) {
          $pattern = substr_replace($pattern, "", $pos, strlen($routePrefix));
        }
      }

      $app->map($route->getMethods(), $intlRoutePrefix . $pattern, $route->getCallable());
    }
  }
}
