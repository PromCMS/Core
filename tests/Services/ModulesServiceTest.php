<?php

use PromCMS\Core\App;
use PromCMS\Core\Module;
use PromCMS\Core\Services\ModulesService;
use PromCMS\Tests\AppTestCase;

final class ModulesServiceTest extends AppTestCase
{
  static App $app;
  static ModulesService $modulesService;
  static string $testProjectRoot;

  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();
    static::$modulesService = static::$app->getSlimApp()->getContainer()->get(ModulesService::class);

    mkdir(Module::$modulesRoot);
  }

  /**
   * @after
   */
  public function clear_out_modules(): void
  {
    $this->deleteAllModules();
  }

  private function getNamesFromArrayOfModules()
  {
    return array_map(
      /**
       * @param Module $module
       */
      fn($module) => $module->getFolderName(),
      static::$modulesService->getAll()
    );
  }

  public function test_getAll_works_correctly()
  {
    $name = "TestTest";
    $this->createModule($name);

    $this->assertEquals(
      [$name],
      $this->getNamesFromArrayOfModules()
    );
  }

  public function test_getAll_does_not_return_disabled()
  {
    $name = "TestTest";
    $this->createModule($name, ["enabled" => false]);

    $this->assertEquals(
      [],
      $this->getNamesFromArrayOfModules()
    );
  }

  public function test_getAll_respects_order()
  {
    $this->createModule("TestTest3");
    $this->createModule("TestTest5");
    $this->createModule("TestTest", ["order" => 2]);
    $this->createModule("TestTest4", ["order" => 3]);
    $this->createModule("TestTest2", ["order" => 1]);

    $this->assertEquals(
      ["TestTest3", "TestTest5", "TestTest2", "TestTest", "TestTest4"],
      $this->getNamesFromArrayOfModules()
    );
  }
}
