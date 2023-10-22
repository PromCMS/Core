<?php

namespace PromCMS\Core\Utils;

use PromCMS\Core\Module;
use PromCMS\Core\Path;

class FsUtils
{
  /**
   * Recursively deletes directory and its contents
   */
  static function rrmdir($dir)
  {
    if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
            static::rrmdir($dir . DIRECTORY_SEPARATOR . $object);
          else
            unlink($dir . DIRECTORY_SEPARATOR . $object);
        }
      }
      rmdir($dir);
    }
  }

  /**
   * Reads file contents from specified path
   * 
   * @param string $fileLocation Location to the json file. 
   *                             This can be relative or absolute file path to filesystem, or location on the internet. 
   *                             This can also be a path with @modules:<module name> prefix to resolve into module right away
   */
  static function readFile(string $fileLocation)
  {
    $path = $fileLocation;

    if (str_starts_with($fileLocation, '@modules:')) {
      $chunks = explode('/', $fileLocation);

      $path = Path::join(
        (new Module($chunks[0]))->getPath(),
        implode('/', array_slice($chunks, 1))
      );
    }

    return file_get_contents($path);
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


  // TODO - deprecate this and use psr autoload instead
  /**
   * Imports all php scripts for specified folder
   * @return string[]|boolean Returns an array of imported paths
   */
  public static function autoloadFolder(string $pathToFolder)
  {
    $importedFilePaths = [];
    if (!is_dir($pathToFolder)) {
      return false;
    }

    $filePaths = static::getDirContents($pathToFolder);
    foreach ($filePaths as $filePath) {
      // do not load dir, this is just one level only
      if (is_dir($filePath)) {
        continue;
      }

      include_once $filePath;
      $importedFilePaths[] = $filePath;
    }

    return $importedFilePaths;
  }

  // TODO: make a better solution to this class search
  /**
   * Auto-loads models for specified module root. This is primarily used by modules.
   * @return string[]|false An array of module names or
   */
  public static function autoloadModels(string $moduleRoot)
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

  public static function autoloadControllers(string $moduleRoot)
  {
    $importedFilepaths = static::autoloadFolder(Path::join($moduleRoot, "Controllers"));

    if (!$importedFilepaths) {
      return false;
    }

    return array_map(function (string $filePath) {
      return basename($filePath, '.controller.php');
    }, $importedFilepaths);
  }
}
