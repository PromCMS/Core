<?php
namespace PromCMS\Core\Bootstrap;

use DI\Container;
use Slim\App;

interface AppModuleInterface {
  public function run(App $app, Container $container);
}