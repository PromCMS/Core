<?php

namespace PromCMS\Core\Services;

use DI\Container;
use PromCMS\Core\Path;
use PromCMS\Core\Module;

class ModulesService {
  private Container $container;

  public function __construct(Container $container) {
    $this->container = $container;
  }

  private static function sortByOrderField(array $left, array $right)
  {
    if (isset($left["order"]) || isset($right["order"])) {
      if (isset($left["order"])  && !isset($right["order"])) {
        $value = intval($left["order"]);

        return $value < 0 ? -1 : ($value > 0  ? 1 : 0);
      }

      if (!isset($left["order"]) && isset($right["order"])) {
        $value = intval($right["order"]);

        return $value < 0 ? -1 : ($value > 0 ? -1 : 0);
      }

      if (isset($left["order"]) && isset($right["order"])) {
        $leftValue = intval($left["order"]);
        $rightValue = intval($right["order"]);

        return $leftValue > $rightValue ? 1 : ($leftValue < $rightValue ? -1 : 0);
      }
    }

    return 0;
  }

  /**
   * @return Module[]
   */
  function getAll() {
    $moduleRoots = glob(
      Path::join($this->container->get('app.root'), 'modules', '*'), 
      GLOB_ONLYDIR
    );

    /**
     * @var Module[]
     */
    $modules = [];

    foreach ($moduleRoots as $moduleRoot) {
      try {
        $module = new Module($moduleRoot);

        if ($module->isEnabled()) {
          $modules[] = $module;
        }
      } catch (\Exception $e) {
        $message = $e->getMessage();
        echo "<b>Warning </b> - Detected in '$moduleRoot', but an error happened during validation: $message";
      }
    }

    usort($modules, 
      /**
       * @param Module $leftModule
       * @param Module $rightModule
       */
      function ($leftModule, $rightModule) {
        $leftOrder = $leftModule->getOrder();
        $rightOrder = $rightModule->getOrder();

        return static::sortByOrderField(
          $leftOrder === null ? [] : ["order" => $leftOrder], 
          $rightOrder === null ? [] : ["order" => $rightOrder]
        );
      }
    );

    return $modules;
  }
}