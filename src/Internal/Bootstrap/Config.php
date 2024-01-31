<?php

namespace PromCMS\Core\Internal\Bootstrap;

use PromCMS\Core\PromConfig;
use Symfony\Component\Dotenv\Dotenv;

use PromCMS\Core\Config as AppConfig;
use PromCMS\Core\Internal\Config\Security as ConfigPart__Security;
use PromCMS\Core\Internal\Config\SecuritySession as ConfigPart__Security__Session;
use PromCMS\Core\Internal\Config\SecurityToken as ConfigPart__Security__Token;
use PromCMS\Core\Internal\Config\Environment as ConfigPart__Environment;
use PromCMS\Core\Internal\Config\System as ConfigPart__System;
use PromCMS\Core\Internal\Config\SystemLogging as ConfigPart__System__Logging;
use Symfony\Component\Filesystem\Path;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
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

    $APP_ENV = $_ENV['APP_ENV'] ?? 'development';
    $RELATIVE_LOGGING_FILEPATH = $_ENV['SYSTEM_LOGGING_PATHNAME'] ?? null;
    $IS_DEV_ENV = $APP_ENV == 'development' || $APP_ENV == 'develop';
    $DEBUG_ENABLED = $IS_DEV_ENV ? true : ($this->getEnvSafely('APP_DEBUG') ?? "false" === "true");

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
      'system' => new ConfigPart__System([
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
