<?php

declare(strict_types=1);

namespace PromCMS\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use PromCMS\Core\App;
use PromCMS\Core\Exceptions\AppException;
use PromCMS\Core\Path;

function rmdir_recursive($dir)
{
  foreach (scandir($dir) as $file) {
    if ('.' === $file || '..' === $file) continue;
    if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
    else unlink("$dir/$file");
  }
  rmdir($dir);
}

final class AppTest extends TestCase
{
  private static String $projectRoot;
  private static String $testProjectRoot;

  // Setup folder and core files
  public static function setUpBeforeClass(): void
  {
    static::$projectRoot = Path::join(__DIR__, "..");
    static::$testProjectRoot = Path::join(static::$projectRoot, ".test");

    if (is_dir(static::$testProjectRoot)) {
      rmdir_recursive(static::$testProjectRoot);
    }
    mkdir(static::$testProjectRoot);
    file_put_contents(Path::join(static::$testProjectRoot, ".env"), "
      APP_NAME=\"PromCMS Test Project\"
      APP_PREFIX=
      APP_KEY=
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
      SECURITY_SESSION_LIFETIME=3600 #1 hour
    ");
  }

  public function testShouldThrowWithoutInit()
  {
    $root = Path::join(__DIR__, "..", ".test");
    $app = new App($root);

    try {
      $app->run();
    } catch (Exception | AppException $error) {
      $this->assertInstanceOf(AppException::class, $error);
      $this->assertEquals('Cannot run application without initializing it', $error->getMessage());
      return;
    }

    throw new Exception("Should throw exception without initialing the app");
  }

  public function testShouldInitializeRight(): void
  {
    $app = new App(static::$testProjectRoot);

    $this->assertClassHasAttribute('root', App::class);

    $app->init(true);
    $container = $app->getSlimApp()->getContainer();

    // Check that all modules are initialized and hooked in container
    foreach ($app->getAppModules() as $moduleClassName) {
      $this->assertInstanceOf($moduleClassName, $container->get($moduleClassName));
    }
  }

  public static function tearDownAfterClass(): void
  {
    rmdir_recursive(static::$testProjectRoot);
  }
}
