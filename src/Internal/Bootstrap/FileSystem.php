<?php

namespace PromCMS\Core\Internal\Bootstrap;

use PromCMS\Core\Filesystem as FileSystemClass;
use PromCMS\Core\Utils\FsUtils;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class FileSystem implements AppModuleInterface
{
  public function run($app, $container)
  {
    FsUtils::$APP_SRC = $container->get('app.src');
    $container->set(FileSystemClass::class, new FileSystemClass($container));
  }
}
