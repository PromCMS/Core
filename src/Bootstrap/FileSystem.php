<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Filesystem as FileSystemClass;

class FileSystem implements AppModuleInterface
{
  public function run($app, $container)
  {
    $container->set(FileSystemClass::class, new FileSystemClass($container));
  }
}
