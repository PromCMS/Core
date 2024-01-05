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
    $promConfig = $container->get(PromConfig::class);
    $databaseConnections = array_values($promConfig->getDatabaseConnections());
    $modelsPaths = [Path::join(__DIR__, '..', '..', 'src', 'Models')];
    
    if (!$promConfig->isCore) {
      $modelsPaths[] = $promConfig->getProjectModuleModelsRoot();
    }

    $config = ORMSetup::createAttributeMetadataConfiguration(
      paths: $modelsPaths,
      isDevMode: true,
    );
    $connection = DriverManager::getConnection($databaseConnections[0], $config);

    $container->set(EntityManager::class, new EntityManager($connection, $config));
  }
}
