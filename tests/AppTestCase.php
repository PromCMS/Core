<?php

namespace PromCMS\Tests;

use PHPUnit\Framework\TestCase;
use PromCMS\Core\Path;
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
}
