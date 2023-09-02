<?php

declare(strict_types=1);

namespace PromCMS\Tests;

use Exception;
use PromCMS\Core\App;
use PromCMS\Core\Exceptions\AppException;

final class AppTest extends AppTestCase
{
  static String $testProjectRoot;

  public function testShouldThrowWithoutInit()
  {
    $app = new App(static::$testProjectRoot);

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

    $this->assertObjectHasProperty('root', $app);

    $app->init(true);
    $container = $app->getSlimApp()->getContainer();

    // Check that all modules are initialized and hooked in container
    foreach ($app->getAppModules() as $moduleClassName) {
      $this->assertInstanceOf($moduleClassName, $container->get($moduleClassName));
    }
  }
}
