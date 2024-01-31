<?php

namespace PromCMS\Core\Utils;

use Symfony\Component\Filesystem\Path;

class FsUtils
{
  public static string $APP_SRC;
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
   *                             This can also be a path with @app/<relative path> prefix to resolve into module right away
   */
  static function readFile(string $fileLocation)
  {
    $path = $fileLocation;

    if (str_starts_with($fileLocation, '@app/')) {
      $chunks = explode('/', $fileLocation);

      $path = Path::join(
        static::$APP_SRC,
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
}
