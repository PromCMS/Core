<?php
namespace PromCMS\Core;

use DI\Container;
use Illuminate\Support\Facades\Config;
use PromCMS\Core\Exceptions\AppException;
use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use Slim\Middleware\Session;

class App {
  private SlimApp $app;
  private string $root;

  function __construct(string $root)
  {
    $this->root = $root;
  }

  public function run() {
    if (!$this->root) {
      throw new AppException("Please define your project root before running the app") ;
    }

    if (!isset($this->app)) {
      // Add dependency container
      $container = new Container();
  
      AppFactory::setContainer($container);
  
      // Create an app
      $this->app = AppFactory::create();
  
      $container->set('session', new \SlimSession\Helper());

      $container->set('app.root', $this->root);
  
      // Add routing middleware
      $this->app->addRoutingMiddleware();
  
      // Add SLIM PHP body parsing middleware
      $this->app->addBodyParsingMiddleware();
  
      $libsToInject = [
        '/libs/config.bootstrap.php',
        '/libs/utils.bootstrap.php',
        '/libs/db.bootstrap.php',
        '/libs/fly-system.bootstrap.php',
        '/libs/mailer.bootstrap.php',
        '/libs/twig.bootstrap.php',
      ];
  
      // Inject core php modules dynamically
      foreach ($libsToInject as $libPath) {
        $lib = require __DIR__ . $libPath;
        $lib($container);
      }
  
      $config = $container->get(Config::class);
      $isDevelopment = $config['env']['development'];
  
      // SLIM PHP error middleware
      $this->app->addErrorMiddleware(
        $config['env']['debug'] || $isDevelopment,
        true,
        true,
      );
  
      // Session
      $this->app->add(
        new Session([
          'name' => 'prom_session',
          'autorefresh' => true,
          'lifetime' => '1 hour',
          'httponly' => !$isDevelopment,
          'secure' => !$isDevelopment,
        ]),
      );
  
      $modulesBootstrap = require_once __DIR__ . '/libs/modules.bootstrap.php';
  
      $modulesBootstrap($this->app, $container);
    }

  }
}