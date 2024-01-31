<?php

namespace PromCMS\Core;

use DI\Container;
use League\Flysystem\Filesystem as FlyFilesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Symfony\Component\Filesystem\Path;

class Filesystem
{
  private static $UPLOADS_KEY = '__prom_uploads';
  private static $IMAGE_CACHE_KEY = '__prom_cached_images';

  private PromConfig $promConfig;
  private string $appRoot;
  private array $fsInstances;

  public function __construct(Container $container)
  {
    $this->promConfig = $container->get(PromConfig::class);
    $this->appRoot = $container->get('app.root');
  }

  /**
   * Creates local filesystem that manages files on disk
   */
  public function createLocal(string $name, string $root)
  {
    $adapter = new LocalFilesystemAdapter(location: $root);
    $filesystem = new FlyFilesystem($adapter, [
      'public_url' => $this->promConfig->getProject()->url->__toString()
    ]);

    $this->fsInstances[$name] = $filesystem;

    return $filesystem;
  }

  /**
   * Gets filesystem by name
   */
  public function with(string $name): FlyFilesystem|null
  {
    return $this->fsInstances[$name] ?? null;
  }

  public function getUploadsRoot(): string
  {
    return Path::join($this->appRoot, 'uploads');
  }

  /**
   * Gets uploads filesystem
   */
  public function withUploads(): FlyFilesystem
  {
    if (empty($item = $this->with(static::$UPLOADS_KEY))) {
      $item = $this->createLocal(static::$UPLOADS_KEY, $this->getUploadsRoot());
    }

    return $item;
  }

  public function getCachedImagesRoot()
  {
    return Path::join($this->appRoot, 'cache', 'images');
  }

  /**
   * Gets uploads filesystem
   */
  public function withCachedImages(): FlyFilesystem
  {
    if (empty($item = $this->with(static::$IMAGE_CACHE_KEY))) {
      $item = $this->createLocal(static::$IMAGE_CACHE_KEY, $this->getCachedImagesRoot());
    }

    return $item;
  }
}