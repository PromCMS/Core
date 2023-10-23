<?php

namespace PromCMS\Core\Services;

use DI\Container;
use Exception;
use League\Flysystem\Filesystem;
use PromCMS\Core\Config;
use PromCMS\Core\Path;

class ImageService
{
  private array $ALLOWED_IMAGE_TYPES = ["jpeg", "jpg", "png", "webp"];
  private string $DEFAULT_IMAGE_TYPE = "jpeg";
  private Filesystem $cacheFs;
  private Filesystem $fs;
  private Config $config;

  public function __construct(Container $container)
  {
    $this->fs = $container->get('filesystem');
    $this->config = $container->get(Config::class);
    $this->cacheFs = $container->get('cache-filesystem');
  }

  /**
   * native imagepng has different quality parameter, but we want to keep the functionality the same across formats. This takes care of that
   */
  private function formatQualityToPNG(int $input): int
  {
    return (min(9, floor($input / 10)) - 9) * -1;
  }

  private function getFileTypeFromMimeType(string $mimeType): null|string
  {
    $sliced = explode('/', $mimeType);
    if (!in_array($sliced[1] ?? "", $this->ALLOWED_IMAGE_TYPES)) {
      return null;
    }

    return $sliced[1];
  }

  private function parseDirtyParamsToGetProcessed(array $dirtyParams): array
  {
    $keysToParser = [
      "q" => fn ($value) => !empty($value) ? max(0, min(100, intval($value))) : null,
      "w" => fn ($value) => !empty($value) ? intval($value) : null,
      "h" => fn ($value) => !empty($value) ? intval($value) : null,
      "f" => fn ($value) => !empty($value) && in_array($value, $this->ALLOWED_IMAGE_TYPES) ? $value : null,
    ];

    $result = [];

    foreach ($dirtyParams as $key => $value) {
      if (in_array($key, array_keys($keysToParser))) {
        $resultFromParser = $keysToParser[$key]($value);

        if (!empty($resultFromParser)) {
          $result[$key] = $resultFromParser;
        }
      }
    }

    if (empty($result["q"])) {
      $result["q"] = 75;
    }

    return $result;
  }

  public function getProcessed(array $fileInfo, $dirtyParams = [])
  {
    $args = $this->parseDirtyParamsToGetProcessed($dirtyParams);
    $file = $this->fs->readStream($fileInfo['filepath']);
    $fileStream = $file;

    if (count($args)) {
      $fileName = basename(
        $fileInfo['filepath'],
        '.' . pathinfo($fileInfo['filepath'], PATHINFO_EXTENSION),
      );

      $fileNameWithArgs = implode('.', [
        implode(
          '&',
          array_map(function ($key) use ($args) {
            $arg = $args[$key];
            return "$key$arg";
          }, array_keys($args)),
        ),
        $fileName,
      ]);
      $transformToType = $args["f"] ?? $this->getFileTypeFromMimeType($fileInfo["mimeType"]) ?? $this->DEFAULT_IMAGE_TYPE;
      $fileBasenameWithArgs = "$fileNameWithArgs.$transformToType";

      if (!$this->cacheFs->fileExists($fileBasenameWithArgs)) {
        $gdImageSource = \imagecreatefromstring(stream_get_contents($file));

        if (isset($args['w'])) {
          if (isset($args['h'])) {
            $gdImageSource = imagescale($gdImageSource, $args['w'], $args['h']);
          } else {
            $gdImageSource = imagescale($gdImageSource, $args['w']);
          }
        }

        $saveToPath = Path::join($this->config->fs->cachePath, $fileBasenameWithArgs);
        switch ($transformToType) {
          case 'jpeg':
          case 'jpg':
            $imageConverted = \imagejpeg(
              $gdImageSource,
              $saveToPath,
              $args['q'],
            );
            break;
          case 'png':
            //                        imagealphablending($gdImageSource, false);
            imagesavealpha($gdImageSource, true);
            $imageConverted = \imagepng(
              $gdImageSource,
              $saveToPath,
              $this->formatQualityToPNG($args['q']),
              PNG_ALL_FILTERS
            );
            break;
          case 'webp':
            imagesavealpha($gdImageSource, true);
            $imageConverted = \imagewebp(
              $gdImageSource,
              $saveToPath,
              $args['q'],
            );
            break;
          default:
            throw new \Exception("Cannot transform image as $transformToType is not supported type");
        }

        if (!$imageConverted) {
          throw new Exception('Failed to format image in ImageService');
        }
      }

      $fileStream = $this->cacheFs->readStream($fileBasenameWithArgs);
    }

    $fileId = $fileInfo['id'];
    $gdImageSource = \imagecreatefromstring(stream_get_contents($fileStream));
    $imageWidth = imagesx($gdImageSource);
    $imageHeight = imagesy($gdImageSource);
    $joinedArgs = implode(
      '&',
      array_map(function ($key) use ($args) {
        $arg = $args[$key];

        return "$key=$arg";
      }, array_keys($args)),
    );

    return [
      'resource' => $fileStream,
      'src' =>
      $this->config->app->baseUrl .
        "/api/entry-types/files/items/$fileId/raw?$joinedArgs",
      'width' => $imageWidth,
      'height' => $imageHeight,
    ];
  }
}
