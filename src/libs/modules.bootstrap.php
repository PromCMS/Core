<?php

use Slim\App;
use DI\Container;
use PromCMS\Core\Config;
use PromCMS\Core\Path;
use PromCMS\Core\Utils;

return function (App $app, Container $container) {
  $appRoot = $container->get('app.root');

  // Make sure that 'Core' module is loaded first
  $moduleNames = Utils::getValidModuleNames($appRoot);
  /** @var Utils */
  $utils = $container->get('utils');
  /** @var Config */
  $config = $container->get(Config::class);

  $filePathsToApiRoutes = [];
  $filePathsToFrontRoutes = [];

  // array of loaded model names (names of classes)
  $loadedModels = [];

  // Simple autoload load module logic
  foreach ($moduleNames as $dirname) {
    $moduleRoot = Utils::getModuleRoot($appRoot, $dirname);
    // Make sure that plugin has valid info file
    $bootstrapFilepath = Path::join($moduleRoot, 'bootstrap.php');
    $bootstrapAfter = Path::join($moduleRoot, 'bootstrap.after.php');
    $apiRoutesFilepath = Path::join($moduleRoot, 'api.routes.php');
    $frontRoutesFilepath = Path::join($moduleRoot, 'front.routes.php');

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

    // Loads controllers beforehand
    $utils->autoloadControllers($moduleRoot);

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

  foreach ([$routePrefix, $intlRoutePrefix] as $routePrefixPart) {
    // Every module should have been bootstrapped by now so we can continue to including custom routes
    $app->group($routePrefixPart, function ($router) use (
      $filePathsToApiRoutes,
      $filePathsToFrontRoutes,
      $app,
      &$importedModules,
      $config
    ) {
      // Load api routes first from prepared set
      $router
        ->group('/api', function ($router) use (
          $filePathsToApiRoutes,
          $app,
          &$importedModules
        ) {
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
};
