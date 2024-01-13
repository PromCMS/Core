<?php

namespace PromCMS\Core\Services;

use DI\Container;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use GuzzleHttp\Psr7\MimeType;
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

  public function __construct(Container $container)
  {
    $this->fs = $container->get(Filesystem::class);
    $this->config = $container->get(Config::class);
    $this->em = $container->get(EntityManager::class);
  }

  private function createQb()
  {
    return $this->em->createQueryBuilder();
  }

  /**
   * Get one specific file from database
   */
  public function getById(string $id): File
  {
    return $this->em->getRepository(File::class)->find($id);
  }

  public function getManyPaged(int $page, int $perPage = 15, Expr|WhereQueryParam|Comparison|Andx|null $where = null)
  {
    $filesQuery = $this->createQb()->from(File::class, 'f')->select('f');

    if (!empty($where)) {
      if ($where instanceof WhereQueryParam) {
        $where->toQuery($filesQuery, 'f');
      } else {
        $filesQuery->where($where);
      }
    }

    return Paginate::fromQuery($filesQuery)->execute($page, $perPage);
  }

  /**
   * Get many files from defined directory
   */
  public function getManyInDirectoryPaged(string $directoryPath, int|null $page = null, int|null $perPage = null, Expr|WhereQueryParam|Comparison|Andx|null $where = null)
  {
    $regexPart =
      $directoryPath . ($directoryPath !== '/' ? '/' : '');


    // $regexPart = str_replace('/', '\/', $fixedDirectoryPath);
    // $where[] = function ($file) use ($regexPart) {
    //   $pattern = '(' . $regexPart . ')[^\/]*(\.).*';
    //   $pattern = '/^' . $pattern . "$/m";
    //   return !!preg_match($pattern, $file['filepath']);
    // };

    if (empty($where)) {
      $where = $this->em->getExpressionBuilder();
    }

    if ($where instanceof WhereQueryParam) {
      $where->add(name: 'filepath', criteria: 'LIKE', value: "$regexPart%");
    } else {
      $where->andX($where->like('filepath', "$regexPart%"));
    }

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

    $this->em->getConnection()->beginTransaction();

    try {
      $createdFile = new File();
      $createdFile->fill($createFileMetadata);
      $this->em->persist($createdFile);

      $this->fs->withUploads()->writeStream($newFilePath, $uploadedFile->getStream()->detach());

      $this->em->flush();
      $this->em->getConnection()->commit();
    } catch (\Exception $error) {
      $this->em->getConnection()->rollBack();

      throw $error;
    }

    return $createdFile;
  }

  public function updateById(string $id, array $payload)
  {
    $existingFileMetadata = $this->getById($id);

    $this->em->getConnection()->beginTransaction();

    try {
      // TODO: Update its filepath to different root
      if (!empty($payload["filepath"])) {
        unset($payload["filepath"]);
      }

      if (!empty($payload["filename"]) && Path::hasExtension($newFilename = $payload["filename"])) {
        $payload["filename"] = Path::changeExtension($newFilename, Path::getExtension($existingFileMetadata->getFilename()));
      }

      $existingFileMetadata->fill($payload);

      $this->em->flush();
      $this->em->getConnection()->commit();
    } catch (\Exception $error) {
      $this->em->getConnection()->rollBack();

      throw $error;
    }

    return $existingFileMetadata;
  }

  public function deleteById(string $id)
  {
    $fileInfo = $this->getById($id);

    try {
      $this->em->remove($fileInfo);
      $this->em->flush();

      $this->fs->withUploads()->delete($fileInfo->getFilepath());

      $this->em->getConnection()->commit();
    } catch (\Exception $error) {
      $this->em->getConnection()->rollBack();

      throw $error;
    }
  }
}
