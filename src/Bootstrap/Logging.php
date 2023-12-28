<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Config;
use PromCMS\Core\Logger;
use PromCMS\Core\PromConfig;
use Slim\App;

class Logging implements AppModuleInterface
{
  public function run(App $app, $container)
  {
    /** @var Config */
    $config = $container->get(Config::class);
    /** @var PromConfig */
    $promConfig = $container->get(PromConfig::class);
    $logger = new Logger($promConfig->getProjectName());

    if (!empty($config->system->logging->logFilepath)) {
      $logger->pushFileHandler($config->system->logging->logFilepath);
    }

    $container->set(Logger::class, $logger);
  }
}
