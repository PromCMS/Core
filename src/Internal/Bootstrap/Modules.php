<?php

namespace PromCMS\Core\Internal\Bootstrap;

use DI\Container;
use PromCMS\Core\Http\Routing\AsRouteGroup;
use PromCMS\Core\Http\Routing\RouteImplementation;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Logger;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Services\ModulesService;
use PromCMS\Core\Services\RenderingService;
use PromCMS\Core\Utils\FsUtils;
use Slim\App;
use PromCMS\Core\Module;
use PromCMS\Core\Config;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Slim\Routing\RouteCollectorProxy as Router;

use PromCMS\Core\Internal\Http\Controllers as InternalControllers;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class Modules implements AppModuleInterface
{
  public function run(App $app, Container $container)
  {
    $appRoot = $container->get('app.root');
    $coreRoot = $container->get('core.root');

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

    $routePrefix = $promConfig->getProject()->url->getPath();
    $supportedLanguages = $promConfig->getProject()->languages;

    // Staticly prepare controllers, no need to search through folder as with modules
    $controllerClassNames = [
      InternalControllers\AdminController::class,
      InternalControllers\EntityController::class,
      InternalControllers\EntitiesController::class,
      InternalControllers\FilesController::class,
      InternalControllers\FoldersController::class,
      InternalControllers\LocalizationController::class,
      InternalControllers\SettingsController::class,
      InternalControllers\SingletonsController::class,
      InternalControllers\UserProfileController::class,
      InternalControllers\UserRolesController::class,
      InternalControllers\UsersController::class,
    ];

    $finder = new Finder();
    $appModulesRoot = Path::join($appRoot, 'modules');

    try {
      $finder->files()->name('*.php')->in([
        Path::join($appModulesRoot, '*', 'Controllers'),
      ])->depth('< 3');

      foreach ($finder as $file) {
        $classNameWithNamespace = $file->getPathname();
        $classNameWithNamespace = str_replace($appModulesRoot, '', $classNameWithNamespace);
        $classNameWithNamespace = str_replace('/', '\\', $classNameWithNamespace);
        $classNameWithNamespace = str_replace('.php', '', $classNameWithNamespace);

        $controllerClassNames[] = "PromCMS\Modules$classNameWithNamespace";
      }
    } catch (\Exception $error) {
      $logger->error('Failed to find controllers in modules', [
        'error' => $error
      ]);
    }

    // Every module should have been bootstrapped by now so we can continue to including custom routes
    $app->group($routePrefix, function (Router $router) use ($controllerClassNames) {
      foreach ($controllerClassNames as $className) {
        $ref = new \ReflectionClass($className);
        $routesPrefix = "";

        $classRouteGroups = $ref->getAttributes(AsRouteGroup::class);
        /** @var \ReflectionAttribute $group */
        if (isset($classRouteGroups[0])) {
          $group = $classRouteGroups[0];

          /** @var AsRouteGroup */
          $groupAsInstance = $group->newInstance();

          $routesPrefix = $groupAsInstance->pathnamePrefix;
        }

        $router->group($routesPrefix, function (Router $innerRouter) use ($ref) {
          $methods = $ref->getMethods();
          /** @var \ReflectionMethod $method */
          foreach ($methods as $method) {
            $routeAttributes = $method->getAttributes(RouteImplementation::class, \ReflectionAttribute::IS_INSTANCEOF);
            $middlewareClasses = array_map(
              fn(\ReflectionAttribute $middlewareMedata) => $middlewareMedata->getArguments()[0],
              array_reverse($method->getAttributes(WithMiddleware::class))
            );
            $methodAddress = $ref->getName() . ":" . $method->getName();

            /** @var \ReflectionAttribute $routeAttribute */
            foreach ($routeAttributes as $routeAttribute) {
              /** @var RouteImplementation */
              $routeMetadata = $routeAttribute->newInstance();
              $route = $routeMetadata->attach($innerRouter, $methodAddress);

              /** @var string $middlewareAttribute */
              foreach ($middlewareClasses as $middlewareClassName) {
                $route->add($middlewareClassName);
              }
            }
          }
        });
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
