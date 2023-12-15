<?php

namespace PromCMS\Core\Services;

use DI\Container;
use GuzzleHttp\Psr7\MimeType;
use GuzzleHttp\Psr7\UploadedFile;
use PromCMS\Core\Config;
use PromCMS\Core\Models\Map\FileTableMap;
use Propel\Runtime\Propel;
use GuzzleHttp\Psr7\Stream;
use League\Flysystem\Filesystem;
use PromCMS\Core\Models\FileQuery;
use PromCMS\Core\Models\File;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\Filesystem\Path;

class FileService
{
  private Filesystem $fs;
  private Config $config;

  public function __construct(Container $container)
  {
    $this->fs = $container->get('filesystem');
    $this->config = $container->get(Config::class);
  }

  /**
   * Get one specific file from database
   */
  public function getById(string $id, $where = []): File
  {
    // TODO
    $andWhere = [];
    $orWhere = [];

    return FileQuery::create()->findPk($id);
  }

  public function getManyPaged(int $page, int $perPage = 15, array $where = [])
  {
    $filesQuery = FileQuery::create();

    foreach ($where as [$field, $criteria, $fieldValue]) {
      $filesQuery->filterBy($field, $fieldValue, $criteria);
    }

    return $filesQuery->paginate($page, $perPage);
  }

  /**
   * Get many files from defined directory
   */
  public function getManyInDirectoryPaged(string $directoryPath, int|null $page = null, int|null $perPage = null, array $where = [])
  {
    if (isset($where['pathname'])) {
      unset($where['pathname']);
    }

    $regexPart =
      $directoryPath . ($directoryPath !== '/' ? '/' : '');


    // $regexPart = str_replace('/', '\/', $fixedDirectoryPath);
    // $where[] = function ($file) use ($regexPart) {
    //   $pattern = '(' . $regexPart . ')[^\/]*(\.).*';
    //   $pattern = '/^' . $pattern . "$/m";
    //   return !!preg_match($pattern, $file['filepath']);
    // };

    $where[] = ["filepath", Criteria::LIKE, "$regexPart%"];

    return $this->getManyPaged($page, $perPage, $where);
  }

  /**
   * Get file from database and return it as a GuzzleHttp Stream
   */
  public function getStreamById(string $id)
  {
    $fileInfo = $this->getById($id);

    return $this->getStream($fileInfo->getData());
  }

  /**
   * Convert file from database to a GuzzleHttp Stream
   */
  public function getStream(array $fileInfo): Stream
  {
    $file = $this->fs->readStream($fileInfo['filepath']);

    return new Stream($file);
  }

  public function create(UploadedFile $uploadedFile, array $fileMetadata)
  {
    $fileConnection = Propel::getWriteConnection(FileTableMap::DATABASE_NAME);

    $fileRoot = $fileMetadata['root'];

    if ($fileRoot === '/') {
      $fileRoot = '';
    }

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

    try {
      $createdFile = new File();

      $createdFile->fromArray($createFileMetadata);
      $createdFile->save($fileConnection);
      $uploadedFile->moveTo(Path::join($this->config->fs->uploadsPath, $newFilePath));

      $fileConnection->commit();
    } catch (\Exception $error) {
      $fileConnection->rollBack();

      throw $error;
    }

    return $createdFile;
  }

  public function updateById(string $id, array $payload)
  {
    $fileConnection = Propel::getWriteConnection(FileTableMap::DATABASE_NAME);
    $existingFileMetadata = $this->getById($id);

    try {
      // TODO: Update its filepath to different root
      if (!empty($payload["filepath"])) {
        unset($payload["filepath"]);
      }

      if (!empty($payload["filename"]) && Path::hasExtension($newFilename = $payload["filename"])) {
        $payload["filename"] = Path::changeExtension($newFilename, Path::getExtension($existingFileMetadata->getFilename()));
      }

      $existingFileMetadata->fromArray($payload);
      $existingFileMetadata->save($fileConnection);
      $fileConnection->commit();
    } catch (\Exception $error) {
      $fileConnection->rollBack();

      throw $error;
    }

    return $existingFileMetadata;
  }

  public function deleteById(string $id)
  {
    $fileInfo = $this->getById($id);
    $fileTransaction = Propel::getWriteConnection(FileTableMap::DATABASE_NAME);
    $fileTransaction->beginTransaction();

    try {
      $fileInfo->delete($fileTransaction);
      $this->fs->delete($fileInfo->getFilepath());

      $fileTransaction->commit();
    } catch (\Exception $error) {
      $fileTransaction->rollBack();

      throw $error;
    }
  }
}
