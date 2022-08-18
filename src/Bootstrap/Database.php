<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Config;
use PromCMS\Core\Database\Model;

class Database implements AppModuleInterface
{
  public function run($app, $container)
  {
    /** @var Config */
    $config = $container->get(Config::class);

    Model::setStoreConfig(
      $config->db->root,
      $config->db->storeConfig,
    );

    Model::$defaultLanguage = $config->i18n->default;
  }
}
