<?php

declare(strict_types=1);

namespace PromCMS\Tests;

use PromCMS\Core\Path;

class TestUtils
{
  public static function prepareSystemForTests(string $root)
  {
    if (is_dir($root)) {
      static::rmdir_recursive($root);
    }
    mkdir($root);
    file_put_contents(Path::join($root, ".env"), "
      APP_NAME=\"PromCMS Test Project\"
      APP_PREFIX=
      APP_KEY=
      APP_DEBUG=true
      APP_URL=http://localhost:3004
      MORE_LANG=\"en,cs,fr\"
      
      MAIL_HOST=\"test\"
      MAIL_PORT=2525
      MAIL_USER=\"test\"
      MAIL_PASS=\"test\"
      MAIL_ADDRESS=\"hi@ondrejlangr.cz\"
      
      SECURITY_SECRET=\"somesecret\"
      SECURITY_TOKEN_LIFETIME=86400 #1 day
      SECURITY_SESSION_LIFETIME=3600 #1 hour
    ");
  }

  public static function generalCleanup(string $root)
  {
    static::rmdir_recursive($root);
  }

  static function rmdir_recursive($dir)
  {
    foreach (scandir($dir) as $file) {
      if ('.' === $file || '..' === $file) continue;
      if (is_dir("$dir/$file")) static::rmdir_recursive("$dir/$file");
      else unlink("$dir/$file");
    }
    rmdir($dir);
  }
}
