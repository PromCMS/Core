<?php

namespace PromCMS\Core;

use DI\Container;
use PromCMS\Core\Exceptions\AppException;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use Slim\Middleware\Session;

use PromCMS\Core\Bootstrap\Config as ConfigBootstrap;
use PromCMS\Core\Bootstrap\Database as DatabaseBootstrap;
use PromCMS\Core\Bootstrap\FlySystem as FlySystemBootstrap;
use PromCMS\Core\Bootstrap\Twig as TwigBootstrap;
use PromCMS\Core\Bootstrap\Modules as ModulesBootstrap;
use PromCMS\Core\Bootstrap\Mailer as MailerBootstrap;
use PromCMS\Core\Bootstrap\Services as ServicesBootstrap;
use PromCMS\Core\Bootstrap\Middlewares as MiddlewaresBootstrap;

/**
 * PromCMS App object
 */
class App
{
  private SlimApp $app;
  private string $root;
  private static array $appModules = [
    ConfigBootstrap::class,
    DatabaseBootstrap::class,
    FlySystemBootstrap::class,
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

      AppFactory::setContainer($container);

      // Create an app
      $this->app = AppFactory::create();

      // Set app root to container
      $container->set('app.root', $this->root);

      if (!$headless) {
        // Add session to container
        $container->set('session', new \SlimSession\Helper());

        // Add routing middleware
        $this->app->addRoutingMiddleware();

        // Add SLIM PHP body parsing middleware
        $this->app->addBodyParsingMiddleware();
      }

      // Run bootstrap classes
      foreach (static::$appModules as $className) {
        (new $className())->run($this->app, $container);
      }

      /** @var Config */
      $config = $container->get(Config::class);
      $isDevelopment = $config->env->development;

      if (!$headless) {
        // SLIM PHP error middleware - we need to add this after  
        $this->app->addErrorMiddleware(
          $config->env->debug || $isDevelopment,
          true,
          true,
        );

        // Add session middleware
        $this->app->add(
          new Session([
            'name' => 'prom_session',
            'autorefresh' => true,
            'lifetime' => '1 hour',
            'httponly' => !$isDevelopment,
            'secure' => !$isDevelopment,
          ]),
        );
      }

      // Initialize modules
      (new ModulesBootstrap())->run($this->app, $container);
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
