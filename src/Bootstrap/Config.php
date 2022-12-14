<?php

namespace PromCMS\Core\Bootstrap;

use SleekDB\Query;
use PromCMS\Core\Path;
use Symfony\Component\Dotenv\Dotenv;

use PromCMS\Core\Config as AppConfig;
use PromCMS\Core\Config\App as ConfigPart__App;
use PromCMS\Core\Config\Security as ConfigPart__Security;
use PromCMS\Core\Config\SecuritySession as ConfigPart__Security__Session;
use PromCMS\Core\Config\SecurityToken as ConfigPart__Security__Token;
use PromCMS\Core\Config\Database as ConfigPart__Database;
use PromCMS\Core\Config\Environment as ConfigPart__Environment;
use PromCMS\Core\Config\Filesystem as ConfigPart__Filesystem;
use PromCMS\Core\Config\i18n as ConfigPart__i18n;
use PromCMS\Core\Config\System as ConfigPart__System;
use PromCMS\Core\Config\SystemModules as ConfigPart__System__Modules;

class Config implements AppModuleInterface
{
  public function run($app, $container)
  {
    $dotenv = new Dotenv();
    $appRoot = $container->get('app.root');
    $dotenv->load(Path::join($appRoot, '.env'));

    $PROM_UPLOADS_ROOT = Path::join($appRoot, 'uploads');
    $PROM_LOCALES_ROOT = Path::join($appRoot, 'locales');
    $PROM_FILE_CACHE_ROOT = Path::join($appRoot, 'cache', 'files');

    $APP_PREFIX = $_ENV['APP_PREFIX'] ? '/' . $_ENV['APP_PREFIX'] : '';
    $APP_ENV = $_ENV['APP_ENV'] ?? 'development';
    $IS_DEV_ENV = $APP_ENV == 'development';
    $DEFAULT_LANGUAGE = $_ENV['LANGUAGE'] ?? 'en';
    $LANGUAGES = array_merge(
      [$DEFAULT_LANGUAGE],
      explode(',', $_ENV['MORE_LANG'] ?? ''),
    );

    $config = new AppConfig([
      'app' => new ConfigPart__App([
        'name' => $_ENV['APP_NAME'] ?? 'PromCMS Project',
        'root' => $appRoot,
        'url' => $_ENV['APP_URL'],
        'prefix' => $APP_PREFIX,
        'baseUrl' => $_ENV['APP_PREFIX']
          ? $_ENV['APP_URL'] . $APP_PREFIX
          : $_ENV['APP_URL'],
      ]),
      'security' => new ConfigPart__Security([
        'session' => new ConfigPart__Security__Session([
          'lifetime' => $_ENV['SECURITY_SESSION_LIFETIME'] ?? 3600,
        ]),
        'token' => new ConfigPart__Security__Token([
          'lifetime' => $_ENV['SECURITY_TOKEN_LIFETIME'] ?? 86400,
        ]),
      ]),
      'db' => new ConfigPart__Database([
        'root' => Path::join($appRoot, '.database'),
        'storeConfig' => [
          'auto_cache' => !$IS_DEV_ENV,
          'cache_lifetime' => $IS_DEV_ENV ? null : 180, // Three minutes
          'timeout' => false,
          'primary_key' => 'id',
          'search' => [
            'min_length' => 2,
            'mode' => 'or',
            'score_key' => 'scoreKey',
            'algorithm' => Query::SEARCH_ALGORITHM['hits'],
          ],
        ],
      ]),
      'env' => new ConfigPart__Environment([
        'development' => $IS_DEV_ENV,
        'debug' => $_ENV['APP_DEBUG'],
        'env' => $APP_ENV,
      ]),
      'fs' => new ConfigPart__Filesystem([
        'cachePath' => $PROM_FILE_CACHE_ROOT,
        'localesPath' => $PROM_LOCALES_ROOT,
        'uploadsPath' => $PROM_UPLOADS_ROOT,
      ]),
      'i18n' => new ConfigPart__i18n([
        'default' => $DEFAULT_LANGUAGE,
        'languages' => $LANGUAGES,
      ]),
      'system' => new ConfigPart__System([
        'modules' => new ConfigPart__System__Modules([
          'modelsFolderName' => 'Models',
          'controllersFolderName' => 'Controllers',
        ]),
      ])
    ]);

    $container->set(AppConfig::class, $config);
  }
}
