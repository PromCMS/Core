<?php

use PromCMS\Core\App;
use PromCMS\Core\Module;
use PromCMS\Core\Utils\FsUtils;
use PromCMS\Tests\AppTestCase;
use Symfony\Component\Filesystem\Path;

final class FsUtilsTest extends AppTestCase
{
  static App $app;
  static string $testProjectRoot;

  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();
  }

  public function test_that_readFile_works_correctly_with_shorthand()
  {
    file_put_contents(Path::join(static::$testProjectRoot, 'schemas', 'test.json'), 'true');

    $this->assertEquals(
      'true',
      FsUtils::readFile('@app:schemas/test.json')
    );
  }
}
