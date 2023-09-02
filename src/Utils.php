<?php

namespace PromCMS\Core;

use Exception;

if (!function_exists('str_ends_with')) {
  function str_ends_with(string $haystack, string $needle): bool
  {
    $needle_len = strlen($needle);
    return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, -$needle_len));
  }
}

class Utils
{
  // TODO: make a better solution to this class search
  /**
   * Auto-loads models for specified module root. This is primarily used by modules.
   * @return string[]|false An array of module names or
   */
  public function autoloadModels(string $moduleRoot)
  {
    // Save previously declared classes in memory
    $classes = get_declared_classes();

    // Autoload files and save imported filepaths to an array
    $importedFilepaths = static::autoloadFolder(Path::join($moduleRoot, "Models"));

    if (!$importedFilepaths) {
      return false;
    }

    // Should have all of loaded model names in array
    $diff = array_values(array_diff(get_declared_classes(), $classes));

    return array_filter($diff, function ($importedName) {
      return !str_ends_with($importedName, 'SingletonModel');
    });
  }

  public function autoloadControllers(string $moduleRoot)
  {
    $importedFilepaths = static::autoloadFolder(Path::join($moduleRoot, "Controllers"));

    if (!$importedFilepaths) {
      return false;
    }

    return array_map(function (string $filePath) {
      return basename($filePath, '.controller.php');
    }, $importedFilepaths);
  }

  /**
   * Get content of defined directory
   */
  public static function getDirContents($dir, &$results = [])
  {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
      $path = realpath(Path::join($dir, $value));
      if (!is_dir($path)) {
        $results[] = $path;
      } elseif ($value != '.' && $value != '..') {
        static::getDirContents($path, $results);
        $results[] = $path;
      }
    }

    return $results;
  }

  /**
   * Imports all php scripts for specified folder
   * @return string[] Returns an array of imported paths
   */
  public static function autoloadFolder(string $pathToFolder)
  {
    $importedFilePaths = [];
    if (!is_dir($pathToFolder)) {
      return false;
    }

    $filePaths = static::getDirContents($pathToFolder);
    foreach ($filePaths as $filePath) {
      include_once $filePath;
      $importedFilePaths[] = $filePath;
    }

    return $importedFilePaths;
  }

  /**
   * Gets the full path to root of module based on its name
   */
  public static function getModuleRoot(string $appRoot, string $moduleName)
  {
    return Path::join($appRoot, 'modules', $moduleName);
  }

  /**
   * Gets all valid modules names (names of their folders) in their defined order
   */
  public static function getValidModuleNames(string $appRoot)
  {
    $moduleNames = array_filter(
      // Map that dirs to folder names
      array_map(
        function ($dir) {
          return basename($dir);
        },
        // Firstly get all the dirs from modules folder
        glob(Path::join($appRoot, 'modules', '*'), GLOB_ONLYDIR),
      ),
      function ($moduleFolderName) use ($appRoot) {
        $moduleRoot = static::getModuleRoot($appRoot, $moduleFolderName);
        /**
         * @var \PromCMS\Core\Module|null
         */
        $module = null;

        try {
          $module = new Module($moduleRoot);
        } catch (\Exception $e) {
          $message = $e->getMessage();
          echo "<b>Warning </b> - Detected in '$moduleRoot', but an error happened during validation: $message";
          return false;
        }

        if ($module->isEnabled() === false) {
          return false;
        }

        return $moduleFolderName !== 'Core';
      },
    );

    usort($moduleNames, function ($leftModuleName, $rightModuleName) use ($appRoot) {
      $leftModuleRoot = static::getModuleRoot($appRoot, $leftModuleName);
      $rightModuleRoot = static::getModuleRoot($appRoot, $rightModuleName);

      $leftModuleInfoPath = Path::join($leftModuleRoot, Module::$moduleInfoFileName);
      $rightModuleInfoPath = Path::join($rightModuleRoot, Module::$moduleInfoFileName);

      $leftModuleInfoContent = (array) json_decode(file_get_contents($leftModuleInfoPath));
      $rightModuleInfoContent = (array) json_decode(file_get_contents($rightModuleInfoPath));

      return static::sortByOrderField($leftModuleInfoContent, $rightModuleInfoContent);
    });

    return array_merge(['Core'], $moduleNames);
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
   * This generates mysql search params
   */
  static function getOnlyOwnersOrEditorsFilter(int $ownerId, $classInstance)
  {
    return !$classInstance->getSummary()->isSharable
      ? ['created_by', '=', $ownerId]
      : [
        ['created_by', '=', $ownerId],
        'OR',
        ["coeditors.$ownerId", '=', true],
      ];
  }
}
