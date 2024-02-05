<?php

namespace PromCMS\Core\Internal\Bootstrap;

use DI\Container;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\ORMSetup;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Internal\Constants;
use PromCMS\Core\PromConfig;
use Symfony\Component\Filesystem\Path;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class Database implements AppModuleInterface
{
  public function run($app, Container $container)
  {
    $appSrc = $container->get('app.src');
    $promConfig = $container->get(PromConfig::class);
    $databaseConnections = $promConfig->getDatabaseConnections();
    $modelsPaths = [Path::join(__DIR__, '..', '..', '..', 'src', 'Database', Constants::MODELS_DIR)];

    $coreIsInVendor = in_array('vendor', explode(DIRECTORY_SEPARATOR, __DIR__));
    if ($coreIsInVendor && file_exists($appModelsPath = Path::join($appSrc, Constants::MODELS_DIR))) {
      $modelsPaths[] = $appModelsPath;
    }

    $config = ORMSetup::createAttributeMetadataConfiguration(
      paths: $modelsPaths,
      isDevMode: true,
    );

    $dsnParser = new DsnParser(['mysql' => 'mysqli', 'postgres' => 'pdo_pgsql', 'sqlite' => 'pdo_sqlite']);
    $connection = DriverManager::getConnection(
      $dsnParser->parse($databaseConnections[0]['uri']),
      $config,
    );

    $container->set(EntityManager::class, new EntityManager($connection, $config));
  }
}
