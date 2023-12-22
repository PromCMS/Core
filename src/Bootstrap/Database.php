<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Config;
use Symfony\Component\Filesystem\Path;

// use PromCMS\Core\Database\Model;

class Database implements AppModuleInterface
{
  public function run($app, $container)
  {
    /** @var Config */
    $config = $container->get(Config::class);
    $appRoot = $container->get('app.root');
    $propelConfigPath = Path::join($appRoot, '.prom-cms', 'propel', 'config', 'config.php');

    if (!file_exists($propelConfigPath)) {
      throw new \Exception("Missing Propel config at '$propelConfigPath', let Propel create config first");
    }

    require_once($propelConfigPath);
    $container->set('propel.root', dirname($propelConfigPath));
  }
}
