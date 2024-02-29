<?php

namespace PromCMS\Core\Database\Models\Listeners;

use DI\Container;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use PromCMS\Core\Database\Models\File;
use PromCMS\Core\Filesystem;

/**
 * @internal Part of PromCMS models
 */
class FileListener
{
  private Filesystem $fs;
  private array $filesToRemove = [];

  private array $filesToMove = [];

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

  public function preUpdate(PreUpdateEventArgs $event)
  {
    if ($event->hasChangedField('filepath')) {
      $this->filesToMove[] = [
        'from' => $event->getOldValue('filepath'),
        'to' => $event->getNewValue('filepath')
      ];
    }
  }

  public function postUpdate(File $file, PostUpdateEventArgs $args)
  {
    $fs = $this->fs->withUploads();

    foreach ($this->filesToMove as $fileToMove) {
      $from = $fileToMove['from'];
      $to = $fileToMove['to'];

      if ($fs->fileExists($from)) {
        $fs->move($from, $to);
      }
    }

    $this->filesToMove = [];
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