<?php

use DI\Container;
use PromCMS\Core\Utils;

return function (Container $container) {
  $utils = new Utils($container);

  $container->set(Utils::class, $utils);
};
