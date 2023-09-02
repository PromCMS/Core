<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Module;
use Slim\Views\Twig as TwigViews;
use Twig\Loader\FilesystemLoader;
use PromCMS\Core\Path;
use PromCMS\Core\Utils;
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
  public function run($app, $container)
  {
    $appRoot = $container->get('app.root');

    // Make sure that 'Core' module is loaded first
    $moduleNames = Utils::getValidModuleNames($appRoot);
    /** @var Utils */
    $utils = $container->get(Utils::class);
    /** @var Config */
    $config = $container->get(Config::class);
    /** @var TwigViews */
    $twig = $container->get(TwigViews::class);
    /** @var FilesystemLoader */
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
    foreach ($moduleNames as $dirname) {
      $moduleRoot = Utils::getModuleRoot($appRoot, $dirname);
      // Make sure that plugin has valid info file
      $bootstrapFilepath = Path::join($moduleRoot, Module::$bootstrapFileName);
      $bootstrapAfter = Path::join($moduleRoot, Module::$afterBootstrapFileName);
      $apiRoutesFilepath = Path::join($moduleRoot, Module::$apiRoutesFileName);
      $frontRoutesFilepath = Path::join($moduleRoot, Module::$frontRoutesFileName);
      $viewsFolderPath = Path::join($moduleRoot, Module::$viewsFolderName);

      // Load bootstrap for that module
      if (file_exists($bootstrapFilepath)) {
        $module = require_once $bootstrapFilepath;

        $module($app);
      }

      // Load models beforehand and save these models to array
      $loadedModuleModels = $utils->autoloadModels($moduleRoot);
      if ($loadedModuleModels) {
        $loadedModels = array_merge($loadedModels, $loadedModuleModels);
      }

      // If we have folder of views then we add another view namespace
      if (is_dir($viewsFolderPath)) {
        $twigFileLoader->addPath($viewsFolderPath, "modules:" . $dirname);
      }

      // Loads controllers beforehand
      $utils->autoloadControllers($moduleRoot);

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
