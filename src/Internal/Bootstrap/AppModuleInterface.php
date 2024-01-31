<?php
namespace PromCMS\Core\Internal\Bootstrap;

use DI\Container;
use Slim\App;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
interface AppModuleInterface
{
  public function run(App $app, Container $container);
}