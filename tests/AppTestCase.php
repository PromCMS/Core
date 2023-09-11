<?php

namespace PromCMS\Tests;

use PHPUnit\Framework\TestCase;
use PromCMS\Core\Module;
use PromCMS\Core\Path;
use PromCMS\Core\Utils\FsUtils;
use PromCMS\Tests\TestUtils;

abstract class AppTestCase extends TestCase
{
  static String $projectRoot;
  static String $testProjectRoot;

  // Setup folder and core files
  public static function setUpBeforeClass(): void
  {
    static::$projectRoot = Path::join(__DIR__, "..");
    static::$testProjectRoot = Path::join(static::$projectRoot, ".test");

    TestUtils::prepareSystemForTests(static::$testProjectRoot);
  }

  public static function tearDownAfterClass(): void
  {
    TestUtils::generalCleanup(static::$testProjectRoot);
  }

  function createModule (String $moduleName, Array $otherData = null) {
    $moduleRoot = Path::join(Module::$modulesRoot, $moduleName);
    mkdir($moduleRoot);

    file_put_contents(Path::join($moduleRoot, Module::$moduleInfoFileName),json_encode(array_merge([
      "name" => $moduleName
    ], $otherData ?? [])));
  }

  function deleteAllModules () {
    FsUtils::rrmdir(Module::$modulesRoot);

    mkdir(Module::$modulesRoot);
  }
}
