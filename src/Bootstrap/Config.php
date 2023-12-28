<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\PromConfig;
use Symfony\Component\Dotenv\Dotenv;

use PromCMS\Core\Config as AppConfig;
use PromCMS\Core\Config\Security as ConfigPart__Security;
use PromCMS\Core\Config\SecuritySession as ConfigPart__Security__Session;
use PromCMS\Core\Config\SecurityToken as ConfigPart__Security__Token;
use PromCMS\Core\Config\Environment as ConfigPart__Environment;
use PromCMS\Core\Config\Filesystem as ConfigPart__Filesystem;
use PromCMS\Core\Config\i18n as ConfigPart__i18n;
use PromCMS\Core\Config\System as ConfigPart__System;
use PromCMS\Core\Config\SystemModules as ConfigPart__System__Modules;
use PromCMS\Core\Config\SystemLogging as ConfigPart__System__Logging;
use Symfony\Component\Filesystem\Path;

class Config implements AppModuleInterface
{
  private function getEnvSafely(string $key): string|null
  {
    if (!isset($_ENV[$key])) {
      return null;
    }

    return $_ENV[$key];
  }

  public function run($app, $container)
  {
    $dotenv = new Dotenv();
    $appRoot = $container->get('app.root');
    $coreRoot = $container->get('core.root');
    $dotenvFilepath = Path::join($appRoot, '.env');

    if (file_exists($dotenvFilepath)) {
      $dotenv->load($dotenvFilepath);
    }

    $PROM_UPLOADS_ROOT = Path::join($appRoot, 'uploads');
    $PROM_LOCALES_ROOT = Path::join($appRoot, 'locales');
    $PROM_FILE_CACHE_ROOT = Path::join($appRoot, 'cache', 'files');

    $APP_PREFIX = !empty($_ENV['APP_PREFIX']) ? '/' . $_ENV['APP_PREFIX'] : '';
    $APP_ENV = $_ENV['APP_ENV'] ?? 'development';
    $RELATIVE_LOGGING_FILEPATH = $_ENV['SYSTEM_LOGGING_PATHNAME'] ?? null;
    $IS_DEV_ENV = $APP_ENV == 'development' || $APP_ENV == 'develop';
    $DEBUG_ENABLED = $IS_DEV_ENV ? true : ($this->getEnvSafely('APP_DEBUG') ?? "false" === "true");
    $LANGUAGES = array_filter(
      explode(',', $_ENV['LANGUAGES'] ?? 'en'),
      function ($item) {
        return is_string($item) && strlen($item);
      }
    );

    $config = new AppConfig([
      'security' => new ConfigPart__Security([
        'session' => new ConfigPart__Security__Session([
          'lifetime' => $this->getEnvSafely('SECURITY_SESSION_LIFETIME'),
          'name' => $this->getEnvSafely('SECURITY_SESSION_NAME')
        ]),
        'token' => new ConfigPart__Security__Token([
          'lifetime' => $this->getEnvSafely('SECURITY_TOKEN_LIFETIME'),
        ]),
      ]),
      'env' => new ConfigPart__Environment([
        'development' => $IS_DEV_ENV,
        'debug' => $DEBUG_ENABLED,
        'env' => $APP_ENV,
      ]),
      'fs' => new ConfigPart__Filesystem([
        'cachePath' => $PROM_FILE_CACHE_ROOT,
        'localesPath' => $PROM_LOCALES_ROOT,
        'uploadsPath' => $PROM_UPLOADS_ROOT,
      ]),
      'i18n' => new ConfigPart__i18n([
        'default' => $LANGUAGES[0],
        'languages' => $LANGUAGES,
      ]),
      'system' => new ConfigPart__System([
        'modules' => new ConfigPart__System__Modules([
          'modelsFolderName' => 'Models',
          'controllersFolderName' => 'Controllers',
        ]),
        'logging' => new ConfigPart__System__Logging([
          'logFilepath' => !empty($RELATIVE_LOGGING_FILEPATH) ? Path::join($appRoot, $RELATIVE_LOGGING_FILEPATH) : null
        ])
      ])
    ]);
    $coreIsInVendor = in_array('vendor', explode(DIRECTORY_SEPARATOR, __DIR__));

    $container->set(AppConfig::class, $config);
    $container->set(PromConfig::class, new PromConfig($coreIsInVendor ? $appRoot : $coreRoot));
  }
}
