<?php

use DI\Container;
use PromCMS\Core\Config;
use PromCMS\Core\Database\Model;

return function (Container $container) {
  /** @var Config */
  $config = $container->get(Config::class);

  Model::setStoreConfig(
    $config->db->root,
    $config->db->storeConfig,
  );

  Model::$defaultLanguage = $config->i18n->language;
};
