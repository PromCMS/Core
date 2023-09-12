<?php

namespace PromCMS\Core\Bootstrap;

use DI\Container;
use PromCMS\Core\Services\ModulesService;
use PromCMS\Core\Utils\FsUtils;
use Slim\App;
use PromCMS\Core\Module;
use Slim\Views\Twig as TwigViews;
use PromCMS\Core\Path;
use PromCMS\Core\Config;
use PromCMS\Core\Http\Routes\ApiRoutes;
use PromCMS\Core\Http\Routes\FrontRoutes;
use PromCMS\Core\Models\GeneralTranslations;
use PromCMS\Core\Models\Settings;
use PromCMS\Core\Models\UserRoles;
use PromCMS\Core\Models\Files;
use PromCMS\Core\Models\Users;

class Modules implements AppModuleInterface
{
  public function run(App $app, Container $container)
  {
    $appRoot = $container->get('app.root');

    Module::$modulesRoot = Path::join($appRoot, 'modules');

    $modules = $container->get(ModulesService::class)->getAll();
    $config = $container->get(Config::class);
    $twig = $container->get(TwigViews::class);
    $twigFileLoader = $twig->getLoader();

    $filePathsToApiRoutes = [];
    $filePathsToFrontRoutes = [];

    // array of loaded model names (names of classes)
    $coreModels = [
      Users::class,
      UserRoles::class,
      Files::class,
      GeneralTranslations::class,
      Settings::class
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
      $loadedModuleModels = FsUtils::autoloadModels($module->getPath());
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

    $routePrefix = $config->app->prefix;
    $supportedLanguages = $config->i18n->languages;
    $importedModules = [];
    $intlRoutePrefix =
      $routePrefix . '/{language:' . implode('|', $supportedLanguages) . '}';

    $coreFrontRoutes = new FrontRoutes($container);
    $coreApiRoutes = new ApiRoutes($container);

    foreach ([$routePrefix, $intlRoutePrefix] as $routePrefixPart) {
      // Every module should have been bootstrapped by now so we can continue to including custom routes
      $app->group($routePrefixPart, function ($router) use (
        $filePathsToApiRoutes,
        $filePathsToFrontRoutes,
        $app,
        $coreFrontRoutes,
        $coreApiRoutes,
        &$importedModules,
        $config
      ) {
        // attach core front routes
        $coreFrontRoutes->attachAllHandlers($router);

        // Load api routes first from prepared set
        $router
          ->group('/api', function ($router) use (
            $filePathsToApiRoutes,
            $app,
            $coreApiRoutes,
            &$importedModules
          ) {
            // attach core api routes
            $coreApiRoutes->attachAllHandlers($router);

            foreach ($filePathsToApiRoutes as $filePath) {
              if (!isset($importedModules[$filePath])) {
                $importedModules[$filePath] = require_once $filePath;
              }

              $importedModules[$filePath]($app, $router);
            }
          })
          ->add(function ($request, $handler) use ($config) {
            $response = $handler->handle($request);
            $responseHeaders = $response->getHeaders();

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
              )
              ->withHeader(
                'Content-Type',
                isset($responseHeaders['Content-Type'])
                  ? $responseHeaders['Content-Type']
                  : 'application/json',
              );
          });

        // Load front routes second - same as api
        foreach ($filePathsToFrontRoutes as $filePath) {
          if (!isset($importedModules[$filePath])) {
            $importedModules[$filePath] = require_once $filePath;
          }

          $importedModules[$filePath]($app, $router);
        }
      });
    }
  }
}
