<?php

namespace PromCMS\Core\Internal\Bootstrap;

use DI\Container;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\ORMSetup;
use Gedmo\Translatable\TranslatableListener;
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
    $modelsPaths = [Path::join(__DIR__, '..', '..', '..', 'src', 'Database', 'Models')];

    if (!$promConfig->isCore) {
      $modelsPaths[] = $promConfig->getProjectModuleModelsRoot();
    }

    $config = ORMSetup::createAttributeMetadataConfiguration(
      paths: $modelsPaths,
      isDevMode: true,
    );

    $eventManager = new EventManager();
    $appDefaultLanguage = $promConfig->getProject()->getDefaultLanguage();

    $translatableListener = new TranslatableListener();
    $translatableListener->setTranslatableLocale($appDefaultLanguage);
    $translatableListener->setDefaultLocale($appDefaultLanguage);
    $eventManager->addEventSubscriber($translatableListener);

    $dsnParser = new DsnParser(['mysql' => 'mysqli', 'postgres' => 'pdo_pgsql', 'sqlite' => 'pdo_sqlite']);
    $connection = DriverManager::getConnection(
      $dsnParser->parse($databaseConnections[0]['uri']),
      $config,
      $eventManager
    );

    $container->set(EntityManager::class, new EntityManager($connection, $config));
  }
}
