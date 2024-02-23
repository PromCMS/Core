<?php

namespace PromCMS\Core;

use DI\Container;
use PromCMS\Core\Exceptions\AppException;
use PromCMS\Core\Internal\Constants;
use Slim\App as SlimApp;
use Slim\Middleware\Session as SessionMiddleware;

use PromCMS\Core\Internal\Bootstrap;
use Symfony\Component\Filesystem\Path;

/**
 * PromCMS App object
 */
class App
{
  private SlimApp $app;
  private string $root;
  private static array $coreBootstraps = [
    Bootstrap\Config::class,
    Bootstrap\Logging::class,
    Bootstrap\Database::class,
    Bootstrap\FileSystem::class,
    Bootstrap\Mailer::class,
    Bootstrap\Services::class,
    Bootstrap\Twig::class,
  ];

  function __construct(string $root)
  {
    $this->root = $root;
  }

  /**
   * Initializes the Application
   * @param bool $headless Defines if current instance should not initialize session related stuff
   */
  public function init(bool $headless = false)
  {
    if (!$this->root) {
      throw new AppException(
        'Please define your project root before running the app',
      );
    }

    if (!isset($this->app)) {
      // Add dependency container
      $container = new Container();

      // Create an app
      $this->app = \DI\Bridge\Slim\Bridge::create($container);

      // Set app root to container
      $container->set('app.root', $this->root);
      $container->set('app.src', $appSrc = Path::join($this->root, 'src'));
      $container->set('core.root', Path::join(__DIR__, '..'));

      // Add session to container
      $container->set(Session::class, new Session());

      // Run bootstrap classes
      foreach (static::$coreBootstraps as $className) {
        (new $className())->run($this->app, $container);
      }

      $appBootstrapFilepath = Path::join($appSrc, Constants::BOOTSTRAP_FILE);
      if (file_exists($appBootstrapFilepath)) {
        $bootstrapClosure = require $appBootstrapFilepath;

        $bootstrapClosure($this->app);
      }

      // Define app middlewares after app bootstrap has been defined, 
      // app middlewares should be run before them
      (new Bootstrap\Middlewares())->run($this->app, $container);
      // Define routes after everything is bootstraped
      (new Bootstrap\Routes())->run($this->app, $container);

      /** @var Config */
      $config = $container->get(Config::class);
      $isDevelopment = $config->env->development;

      // Add routing middleware
      $this->app->addRoutingMiddleware();

      // Add SLIM PHP body parsing middleware
      $this->app->addBodyParsingMiddleware();

      if (!$headless) {
        // SLIM PHP error middleware - we need to add this after  
        $this->app->addErrorMiddleware(
          $config->env->debug || $isDevelopment,
          true,
          true,
        );

        // Add session middleware
        $this->app->add(
          new SessionMiddleware([
            'autorefresh' => true,
            'name' => $config->security->session->name,
            'lifetime' => $config->security->session->lifetime,
            'httponly' => !$isDevelopment,
            'secure' => !$isDevelopment,
          ]),
        );
      }
    }
  }

  /**
   * Returns slim app instance
   */
  public function getSlimApp()
  {
    return $this->app;
  }

  /**
   * Unset the slim app 
   */
  public function destroySlimApp()
  {
    unset($this->app);
  }

  public function getAppModules()
  {
    return static::$coreBootstraps;
  }

  /**
   * Run the app instance
   * @throws AppException in case the app is not initialized first
   */
  public function run()
  {
    if (!isset($this->app)) {
      throw new AppException('Cannot run application without initializing it');
    }

    $this->app->run();
  }
}
