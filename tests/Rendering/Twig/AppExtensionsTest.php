<?php

use PromCMS\Core\App;
use PromCMS\Core\Rendering\Twig\AppExtensions;
use PromCMS\Tests\AppTestCase;

final class AppExtensionsTest extends AppTestCase
{
  static AppExtensions $extensionInstance;
  static string $testProjectRoot;
  static App $app;

  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    static::$extensionInstance = new AppExtensions(static::$app->getSlimApp()->getContainer());
  }


  public function testTwigFunctionGetViteAssetsShouldReturnOnWrongConfig()
  {
    $res = static::$extensionInstance->getViteAssets([
      "distFolderPath" => "dist",
    ]);

    echo $res;

    $this->expectOutputString("<script>alert('Invalid assets array in getViteAssets twig function, because: entries(The property entries is required)');</script>");
  }

  public function testTwigFunctionGetViteAssetsShouldReturnRightOnCorrectConfig()
  {
    $res = static::$extensionInstance->getViteAssets([
      "distFolderPath" => "dist",
      "entries" => [
        [
          "path" => 'index.ts'
        ],
        [
          "path" => 'index.tsx'
        ],
      ]
    ]);


    echo $res;

    $this->expectOutputString("\n<script type=\"module\" crossorigin src=\"/index.ts\"></script>\n<script type=\"module\" crossorigin src=\"/index.tsx\"></script>");
  }
}
