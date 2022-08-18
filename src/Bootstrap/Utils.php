<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Utils as AppUtils;

class Utils implements AppModuleInterface
{
  public function run($app, $container)
  {
    $utils = new AppUtils($container);

    $container->set(AppUtils::class, $utils);
  }
}
