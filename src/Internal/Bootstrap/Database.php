<?php

namespace PromCMS\Core\Internal\Bootstrap;

use DI\Container;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\ORMSetup;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\PromConfig;
use Symfony\Component\Filesystem\Path;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class Database implements AppModuleInterface
{
  public function run($app, Container $container)
  {
    $promConfig = $container->get(PromConfig::class);
    $databaseConnections = $promConfig->getDatabaseConnections();
    $modelsPaths = [Path::join(__DIR__, '..', '..', 'src', 'Models')];

    if (!$promConfig->isCore) {
      $modelsPaths[] = $promConfig->getProjectModuleModelsRoot();
    }

    $config = ORMSetup::createAttributeMetadataConfiguration(
      paths: $modelsPaths,
      isDevMode: true,
    );

    $dsnParser = new DsnParser(['mysql' => 'mysqli', 'postgres' => 'pdo_pgsql', 'sqlite' => 'pdo_sqlite']);
    $connection = DriverManager::getConnection(
      $dsnParser->parse($databaseConnections[0]['uri']),
      $config
    );

    $container->set(EntityManager::class, new EntityManager($connection, $config));
  }
}
