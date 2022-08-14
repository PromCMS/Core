<?php

namespace PromCMS\Core;

use Exception;
use DI\Container;

class Utils
{
  private string $modelsFolderName;
  private string $controllersFolderName;

  public function __construct(Container $container)
  {
    /** @var Config */
    $config = $container->get(Config::class);
    $modulesConfig = $config->system->modules;

    $this->modelsFolderName = $modulesConfig->modelsFolderName;
    $this->controllersFolderName = $modulesConfig->controllersFolderName;
  }

  /**
   * Auto-loads models for specified module root. This is primarily used by modules.
   * @return string[]|false An array of module names or
   */
  public function autoloadModels(string $moduleRoot)
  {
    // Save previously declared classes in memory
    $classes = get_declared_classes();

    // Autoload files and save imported filepaths to an array
    $importedFilepaths = static::autoloadFolder(Path::join($moduleRoot, $this->modelsFolderName));

    if (!$importedFilepaths) {
      return false;
    }

    // Should have all of loaded model names in array
    return array_values(array_diff(get_declared_classes(), $classes));
  }

  public function autoloadControllers(string $moduleRoot)
  {
    $importedFilepaths = static::autoloadFolder(Path::join($moduleRoot, $this->controllersFolderName));

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
      function ($moduleName) use ($appRoot) {
        $moduleInfoFileName  = 'module-info.json';
        $moduleRoot = static::getModuleRoot($appRoot, $moduleName);
        $moduleInfoPath = Path::join($moduleRoot, $moduleInfoFileName);

        try {
          if (!file_exists($moduleInfoPath)) {
            throw new Exception("Module must have $moduleInfoFileName file created");
          }

          $moduleInfoContent = (array) json_decode(file_get_contents($moduleInfoPath));

          if (!$moduleInfoContent) {
            throw new Exception("Not a valid module info");
          }

          if (!isset($moduleInfoContent["name"])) {
            throw new Exception("Please define your module name in $moduleInfoFileName");
          }
        } catch (\Exception $e) {
          $message = $e->getMessage();
          echo "<b>Warning </b> - Detected module by the name '$moduleName', but an error happened during validation: $message";
          return false;
        }

        return $moduleName !== 'Core';
      },
    );

    usort($moduleNames, function ($leftModuleName, $rightModuleName) use ($appRoot) {
      $leftModuleRoot = static::getModuleRoot($appRoot, $leftModuleName);
      $rightModuleRoot = static::getModuleRoot($appRoot, $rightModuleName);

      $leftModuleInfoPath = Path::join($leftModuleRoot, 'module-info.json');
      $rightModuleInfoPath = Path::join($rightModuleRoot, 'module-info.json');

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

      if (!isset($left["order"])  && isset($right["order"])) {
        $value = intval($right["order"]);

        return $value < 0 ? -1 : ($value > 0  ? 1 : 0);
      }

      if (isset($left["order"])  && isset($right["order"])) {
        $leftValue = intval($left["order"]);
        $rightValue = intval($right["order"]);

        return $leftValue > $rightValue ? 1 : ($leftValue < $rightValue ? -1 : 0);
      }
    }

    return 0;
  }
}
