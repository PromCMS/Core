<?php

use PromCMS\Core\FsUtils;
use PromCMS\Core\Utils;
use PromCMS\Tests\AppTestCase;
use PromCMS\Core\Path;

final class UtilsTest extends AppTestCase
{
  static String $testProjectRoot;
  static String $modulesRoot;

  private function createModule (String $moduleName, Array $otherData = null) {
    $moduleRoot = Path::join(static::$modulesRoot, $moduleName);
    mkdir($moduleRoot);

    file_put_contents(Path::join($moduleRoot, 'module-info.json'),json_encode(array_merge([
      "name" => $moduleName
    ], $otherData ?? [])));
  }

  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();
    static::$modulesRoot = Path::join(static::$testProjectRoot, "modules");

    mkdir(static::$modulesRoot);
  }

  /**
  * @after
  */
  public function clear_out_modules(): void
  {
    FsUtils::rrmdir(static::$modulesRoot);
    mkdir(static::$modulesRoot);
  }


  public function test_getValidModuleNames_works_correctly () {
    $name = "TestTest";
    $this->createModule($name);

    $this->assertEquals(
      ["Core", $name],
      Utils::getValidModuleNames(static::$testProjectRoot)
    );
  } 

  public function test_getValidModuleNames_do_not_return_disabled () {
    $name = "TestTest";
    $this->createModule($name, ["enabled" => false]);

    $this->assertEquals(
      ["Core"],
      Utils::getValidModuleNames(static::$testProjectRoot)
    );
  } 

  public function test_getValidModuleNames_respects_order () {
    $this->createModule("TestTest3");
    $this->createModule("TestTest5");
    $this->createModule("TestTest", ["order" => 2]);
    $this->createModule("TestTest4", ["order" => 3]);
    $this->createModule("TestTest2", ["order" => 1]);

    $this->assertEquals(
      ["Core", "TestTest3","TestTest5", "TestTest2", "TestTest", "TestTest4"],
      Utils::getValidModuleNames(static::$testProjectRoot)
    );
  } 
}
