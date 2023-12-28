<?php

namespace PromCMS\Core\Bootstrap;

use DI\Container;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\ORMSetup;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\PromConfig;
use Symfony\Component\Filesystem\Path;

class Database implements AppModuleInterface
{
  public function run($app, Container $container)
  {
    $databaseConnections = array_values($container->get(PromConfig::class)->getDatabaseConnections());

    $config = ORMSetup::createAttributeMetadataConfiguration(
      // TODO: load models from each module
      paths: [Path::join(__DIR__, '..', '..', 'src', 'Models')],
      isDevMode: true,
    );
    $connection = DriverManager::getConnection($databaseConnections[0], $config);

    $container->set(EntityManager::class, new EntityManager($connection, $config));
  }
}
