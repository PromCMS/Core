<?php

use PromCMS\Core\App;
use PromCMS\Core\Rendering\Twig\AppExtensions;
use PromCMS\Tests\AppTestCase;

final class AppExtensionTest extends AppTestCase
{
  static AppExtensions $extensionInstance;
  static String $testProjectRoot;
  static App $app;

  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    static::$app = new App(static::$testProjectRoot);
    static::$app->init(true);

    static::$extensionInstance = new AppExtensions(static::$app->getSlimApp()->getContainer());
  }


  public function testTwigFunctionGetViteAssetsShouldReturnOnWrongConfig()
  {
    $res = static::$extensionInstance->getViteAssets([
      "distFolderPath" => "dist",
    ]);
    echo $res;

    $this->expectOutputString("<script>alert('Invalid assets array in getViteAssets twig function');</script>");
  }

  public function testTwigFunctionGetViteAssetsShouldReturnRightOnCorrectConfig()
  {
    $res = static::$extensionInstance->getViteAssets([
      "distFolderPath" => "dist",
      "assets" => [
        [
          "path" => 'index.ts',
          "type" => "script"
        ],
        [
          "path" => 'index.tsx',
          "type" => "script",
          "scriptType" => "sdfdsf"
        ],
        [
          "path" => 'index.scss',
          "type" => "stylesheet"
        ],
      ]
    ]);


    echo $res;

    $this->expectOutputString("\n <script type=\"module\" crossorigin src=\"index.ts\"></script>\n <script type=\"sdfdsf\" crossorigin src=\"index.tsx\"></script>\n <link rel=\"stylesheet\" href=\"index.scss\">");
  }
}
