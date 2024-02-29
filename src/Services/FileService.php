<?php

namespace PromCMS\Core\Services;

use DI\Container;
use GuzzleHttp\Psr7\MimeType;
use League\Flysystem\StorageAttributes;
use Slim\Psr7\UploadedFile;
use PromCMS\Core\Config;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Paginate;
use PromCMS\Core\Filesystem;
use GuzzleHttp\Psr7\Stream;
use PromCMS\Core\Http\WhereQueryParam;
use PromCMS\Core\Database\Models\File;
use Symfony\Component\Filesystem\Path;

class FileService
{
  private Filesystem $fs;
  private Config $config;
  private EntityManager $em;
  private \League\Flysystem\Filesystem $uploadsFs;

  public function __construct(Container $container)
  {
    $this->fs = $container->get(Filesystem::class);
    $this->config = $container->get(Config::class);
    $this->em = $container->get(EntityManager::class);
    $this->uploadsFs = $container->get(Filesystem::class)->withUploads();
  }

  private function createQb()
  {
    return $this->em->createQueryBuilder();
  }

  /**
   * Get one specific file from database
   */
  public function getById(string|int $id): File
  {
    return $this->em->getRepository(File::class)->find($id);
  }

  public function getManyPaged(int $page, int $perPage = 15, WhereQueryParam|null $where = null)
  {
    $filesQuery = $this->createQb()->from(File::class, 'f')->select('f');

    if (!empty($where)) {
      $where->toQuery($filesQuery, 'f');
    }

    return Paginate::fromQuery($filesQuery)->execute($page, $perPage);
  }

  /**
   * Get many files from defined directory
   */
  public function getManyInDirectoryPaged(string $directoryPath, int|null $page = null, int|null $perPage = null, WhereQueryParam|null $where = null)
  {
    if (empty($where)) {
      $where = new WhereQueryParam("");
    }

    $filesInCurrentDirectory = $this->uploadsFs
      ->listContents($directoryPath)
      ->filter(fn(StorageAttributes $attributes) => $attributes->isFile())
      ->map(fn(StorageAttributes $attributes) => "/" . $attributes->path())
      ->toArray();

    $where->add(name: 'filepath', criteria: 'IN', value: $filesInCurrentDirectory);

    return $this->getManyPaged($page, $perPage, $where);
  }

  /**
   * Get file from database and return it as a GuzzleHttp Stream
   */
  public function getStreamById(string $id)
  {
    $fileInfo = $this->getById($id);

    return $this->getStream($fileInfo);
  }

  /**
   * Convert file from database to a GuzzleHttp Stream
   */
  public function getStream(File $file): Stream
  {
    $file = $this->fs->withUploads()->readStream($file->getFilepath());

    return new Stream($file);
  }

  public function create(UploadedFile $uploadedFile, array $fileMetadata)
  {
    $fileRoot = $fileMetadata['root'];

    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $newBasename = bin2hex(random_bytes(8)) . '-' . time();

    $oldFilename = $uploadedFile->getClientFilename();
    $newFilename = sprintf('%s.%0.8s', $newBasename, $extension);
    $newFilePath = trim(Path::join($fileRoot, $newFilename));

    $createFileMetadata = [
      'filepath' => $newFilePath,
      'filename' => $oldFilename,
      'mimeType' => MimeType::fromFilename($newFilename),
    ];

    if (!empty($data['description'])) {
      $createFileMetadata['description'] = $fileMetadata['description'];
    }

    if (isset($data['private']) && is_bool($fileMetadata['private'])) {
      $createFileMetadata['private'] = $fileMetadata['private'];
    } else {
      $createFileMetadata['private'] = false;
    }

    $this->em->getConnection()->beginTransaction();

    try {
      $createdFile = new File();
      $createdFile->fill($createFileMetadata);
      $this->em->persist($createdFile);

      $this->em->flush();
      $this->em->getConnection()->commit();
      $this->fs->withUploads()->writeStream($newFilePath, $uploadedFile->getStream()->detach());
    } catch (\Exception $error) {
      $this->em->getConnection()->rollBack();

      throw $error;
    }

    return $createdFile;
  }

  public function updateById(string $id, array $payload)
  {
    $existingFileMetadata = $this->getById($id);

    // TODO: Update its filepath to different root
    if (!empty($payload["filepath"])) {
      unset($payload["filepath"]);
    }

    if (!empty($payload["filename"]) && Path::hasExtension($newFilename = $payload["filename"])) {
      $payload["filename"] = Path::changeExtension($newFilename, Path::getExtension($existingFileMetadata->getFilename()));
    }

    $existingFileMetadata->fill($payload);

    $this->em->flush();

    return $existingFileMetadata;
  }

  public function deleteById(string $id)
  {
    $fileInfo = $this->getById($id);
    $this->em->remove($fileInfo);
    $this->em->flush();
  }
}
