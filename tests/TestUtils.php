<?php

declare(strict_types=1);

namespace PromCMS\Tests;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;


class TestUtils
{

  public static $sqliteInitial = "";

  public static function ensureSession()
  {
    if (!isset($_SESSION)) {
      session_start();
    }
  }

  public static function clearSession()
  {
    static::ensureSession();
    session_destroy();
    $_SESSION = [];
  }

  public static function ensureEmptyDatabase(string $root)
  {
    
  }

  public static function prepareSystemForTests(string $root)
  {
    if (is_dir($root)) {
      static::rmdir_recursive($root);
    }
    mkdir($root);
    file_put_contents(Path::join($root, ".env"), "
      APP_NAME=\"PromCMS Test Project\"
      APP_DEBUG=true
      APP_URL=http://localhost:3004
      LANGUAGE=\"en\"
      MORE_LANG=\"cs,fr\"
      
      MAIL_HOST=\"test\"
      MAIL_PORT=2525
      MAIL_USER=\"test\"
      MAIL_PASS=\"test\"
      MAIL_ADDRESS=\"hi@ondrejlangr.cz\"
      
      SECURITY_SECRET=\"somesecret\"
      SECURITY_TOKEN_LIFETIME=86400 #1 day
    ");

    $fileSystem = new Filesystem();
    $fileSystem->mirror(Path::join(__DIR__, '..', '.prom-cms'), Path::join($root, '.prom-cms'));
  }

  private static function getSqlitePath(string $root)
  {
    return Path::join($root, '.prom-cms', 'parsed', 'database.sqlite');
  }

  public static function generalCleanup(string $root)
  {
    static::rmdir_recursive($root);
  }

  static function rmdir_recursive($dir)
  {
    foreach (scandir($dir) as $file) {
      if ('.' === $file || '..' === $file)
        continue;
      if (is_dir("$dir/$file"))
        static::rmdir_recursive("$dir/$file");
      else
        unlink("$dir/$file");
    }
    rmdir($dir);
  }
}