<?php

use PromCMS\Core\App;
use PromCMS\Core\Module;
use PromCMS\Core\Services\ModulesService;
use PromCMS\Core\Utils\FsUtils;
use PromCMS\Tests\AppTestCase;
use PromCMS\Core\Path;

final class FsUtilsTest extends AppTestCase
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

  public function test_that_readFile_works_correctly_with_shorthand()
  {
    $moduleName = 'TestTest';
    $moduleRoot = Path::join(static::$testProjectRoot, 'modules', $moduleName);
    $this->createModule($moduleName);
    file_put_contents(Path::join($moduleRoot, 'test.json'), 'true');

    $this->assertEquals(
      'true',
      FsUtils::readFile('@modules:TestTest/test.json')
    );
  }
}
