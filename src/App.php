<?php

namespace PromCMS\Core;

use DI\Container;
use PromCMS\Core\Exceptions\AppException;
use Slim\App as SlimApp;
use Slim\Middleware\Session as SessionMiddleware;

use PromCMS\Core\Internal\Bootstrap\Config as ConfigBootstrap;
use PromCMS\Core\Internal\Bootstrap\Logging as LoggingBootstrap;
use PromCMS\Core\Internal\Bootstrap\Database as DatabaseBootstrap;
use PromCMS\Core\Internal\Bootstrap\FileSystem as FileSystemBootstrap;
use PromCMS\Core\Internal\Bootstrap\Twig as TwigBootstrap;
use PromCMS\Core\Internal\Bootstrap\Modules as ModulesBootstrap;
use PromCMS\Core\Internal\Bootstrap\Mailer as MailerBootstrap;
use PromCMS\Core\Internal\Bootstrap\Services as ServicesBootstrap;
use PromCMS\Core\Internal\Bootstrap\Middlewares as MiddlewaresBootstrap;
use Symfony\Component\Filesystem\Path;

/**
 * PromCMS App object
 */
class App
{
  private SlimApp $app;
  private string $root;
  private static array $appModules = [
    ConfigBootstrap::class,
    LoggingBootstrap::class,
    DatabaseBootstrap::class,
    FileSystemBootstrap::class,
    MailerBootstrap::class,
    ServicesBootstrap::class,
    TwigBootstrap::class,
    MiddlewaresBootstrap::class
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
      $container->set('core.root', Path::join(__DIR__, '..'));

      // Run bootstrap classes
      foreach (static::$appModules as $className) {
        (new $className())->run($this->app, $container);
      }

      /** @var Config */
      $config = $container->get(Config::class);
      $isDevelopment = $config->env->development;

      // Add session to container
      $container->set(Session::class, new Session());

      // Initialize modules
      (new ModulesBootstrap())->run($this->app, $container);

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
    return static::$appModules;
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
