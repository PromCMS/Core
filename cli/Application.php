<?php

namespace PromCMS\Cli;

use PromCMS\Core\App;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Filesystem\Path;

class Application extends SymfonyApplication
{
  private static $createdApplications = [];

  public static function getPromApp(string $cwd)
  {
    if (empty(static::$createdApplications[$cwd])) {
      $promApp = new App($cwd);
      $promApp->init(true);

      static::$createdApplications[$cwd] = $promApp;
    }

    return static::$createdApplications[$cwd];
  }

  public static function getPromCoreRoot()
  {
    return Path::join(__DIR__, '..', '..');
  }

  public static function isBeingRunInsideApp()
  {
    $filePath = __FILE__;

    return strpos($filePath, 'vendor') !== false;
  }

}