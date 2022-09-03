<?php

namespace PromCMS\Core\Bootstrap;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PromCMS\Core\Config;

class FlySystem implements AppModuleInterface
{
  public function run($app, $container)
  {
    /** @var Config */
    $config = $container->get(Config::class);
    $fsConfig = $config->fs;

    // Adapter init
    $adapter = new LocalFilesystemAdapter($fsConfig->uploadsPath);
    $filesystem = new Filesystem($adapter);

    // Cache path
    $fileCacheAdapter = new LocalFilesystemAdapter($fsConfig->cachePath);
    $fileCacheFilesystem = new Filesystem($fileCacheAdapter);

    // TODO: Do not create this much fs instance - we need to create one and think about different solution
    $container->set('filesystem', $filesystem);
    $container->set('cache-filesystem', $fileCacheFilesystem);
  }
}
