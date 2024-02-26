<?php

namespace PromCMS\Core\Database\Models\Listeners;

use DI\Container;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use PromCMS\Core\Database\Models\File;
use PromCMS\Core\Filesystem;

/**
 * @internal Part of PromCMS models
 */
class FileListener
{
  private Filesystem $fs;
  private array $filesToRemove = [];

  public function __construct(Container $container)
  {
    $this->fs = $container->get(Filesystem::class);
  }

  public function preRemove(File $file, PreRemoveEventArgs $event)
  {
    if (!in_array($file->getFilename(), $this->filesToRemove)) {
      $this->filesToRemove[] = $file->getFilepath();
    }
  }

  public function postRemove()
  {
    $uploads = $this->fs->withUploads();
    foreach ($this->filesToRemove as $filepathToRemove) {
      if ($uploads->fileExists($filepathToRemove)) {
        $this->fs->withUploads()->delete($filepathToRemove);
      }
    }

    $this->filesToRemove = [];
  }
}