<?php

use DI\Container;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PromCMS\Core\Config;

return function (Container $container) {
  /** @var Config */
  $config = $container->get(Config::class);
  $fsConfig = $config->fs;

  // Adapter init
  $adapter = new LocalFilesystemAdapter($fsConfig->uploadsPath);
  $filesystem = new Filesystem($adapter);

  // Locales path
  $localesAdapter = new LocalFilesystemAdapter($fsConfig->localesPath);
  $localesFilesystem = new Filesystem($localesAdapter);

  // Cache path
  $fileCacheAdapter = new LocalFilesystemAdapter($fsConfig->cachePath);
  $fileCacheFilesystem = new Filesystem($fileCacheAdapter);

  // TODO: Do not create this much fs instance - we need to create one and think about different solution
  // Set locales filesystem module and attach it to container
  $container->set('filesystem', $filesystem);
  $container->set('locales-filesystem', $localesFilesystem);
  $container->set('file-cache-filesystem', $fileCacheFilesystem);
};
