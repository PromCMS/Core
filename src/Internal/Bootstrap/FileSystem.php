<?php

namespace PromCMS\Core\Internal\Bootstrap;

use PromCMS\Core\Filesystem as FileSystemClass;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class FileSystem implements AppModuleInterface
{
  public function run($app, $container)
  {
    $container->set(FileSystemClass::class, new FileSystemClass($container));
  }
}
