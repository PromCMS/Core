<?php

namespace PromCMS\Core\Controllers;

use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use GuzzleHttp\Psr7\MimeType;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\UploadedFile;
use League\Flysystem\Filesystem;
use PromCMS\Core\Config;
use PromCMS\Core\HttpUtils;
use PromCMS\Core\Models\Files;
use PromCMS\Core\Services\EntryTypeService;
use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\ImageService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FilesController
{
  private $container;
  private Config $config;
  private Filesystem $fs;
  private FileService $fileService;
  private ImageService $imageService;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $this->fs = $container->get('filesystem');
    $this->fileService = $container->get(FileService::class);
    $this->imageService = $container->get(ImageService::class);
    $this->config = $container->get(Config::class);
  }

  public function getInfo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    HttpUtils::prepareJsonResponse($response, (array) (new Files())->getSummary());

    return $response;
  }

  public function get(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    try {
      HttpUtils::prepareJsonResponse(
        $response,
        $this->fileService->getById($args['itemId'])->getData(),
      );

      return $response;
    } catch (\Exception $e) {
      if ($e instanceof EntityNotFoundException) {
        return $response->withStatus(404);
      }

      throw $e;
    }
  }

  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $queryParams = $request->getQueryParams();
    $service = new EntryTypeService(new Files());
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $limit = intval($queryParams['limit'] ?? 15);
    $where = [];

    if (isset($queryParams['where'])) {
      [$where] = HttpUtils::normalizeWhereQueryParam($queryParams['where']);
    }

    if (isset($queryParams['path'])) {
      $directoryPath = $queryParams['path'];
      $fixedDirectoryPath =
        $directoryPath . ($directoryPath !== '/' ? '/' : '');
      $regexPart = str_replace('/', '\/', $fixedDirectoryPath);
      $where[] = function ($file) use ($regexPart) {
        $pattern = '(' . $regexPart . ')[^\/]*(\.).*';
        $pattern = '/^' . $pattern . "$/m";
        return !!preg_match($pattern, $file['filepath']);
      };
    }

    $response
      ->getBody()
      ->write(json_encode($service->getMany($where, $page, $limit)));

    return $response;
  }

  public function getFile(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $id = $args['itemId'];
    $queryParams = $request->getQueryParams();

    try {
      $userId = $this->container->get('session')->get('user_id', false);
      $fileInfo = $this->fileService->getById($id);

      if ($fileInfo->private && !$userId) {
        return $response->withStatus(401);
      }

      if (preg_match('/image\/.*/', $fileInfo->mimeType)) {
        $imageResource = $this->imageService->getProcessed(
          $fileInfo->getData(),
          $queryParams,
        );
        $stream = new Stream($imageResource['resource']);
      } else {
        $stream = $this->fileService->getStream($fileInfo->getData());
      }

      return $response
        ->withHeader('Content-Type', $this->fs->mimeType($fileInfo->filepath))
        ->withHeader('Content-Length', $stream->getSize())
        ->withHeader('Cache-Control', 'max-age=31536000')
        ->withBody($stream);
    } catch (\Exception $e) {
      if ($e instanceof EntityNotFoundException) {
        return $response->withStatus(404);
      }

      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', $e->getMessage());
    }
  }

  public function create(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $queryParams = $request->getQueryParams();
    $files = $request->getUploadedFiles();

    if (!count($files) || !isset($files['file'])) {
      return $response
        ->withStatus(422)
        ->withHeader('Content-Description', 'No files provided');
    }

    $data = $queryParams ? $queryParams : [];

    /**
     * @var UploadedFile
     */
    $file = $files['file'];

    // If theres an error on upload
    if ($file->getError() !== UPLOAD_ERR_OK) {
      return $response
        ->withStatus(500)
        ->withHeader(
          'Content-Description',
          'Upload failed ' . $file->getError(),
        );
    }

    if (!isset($data['root'])) {
      throw new \Exception('root param not provided');
    }

    $fileRoot = $data['root'];

    $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
    $newBasename = bin2hex(random_bytes(8)) . '-' . time();

    $oldFilename = $file->getClientFilename();
    $newFilename = sprintf('%s.%0.8s', $newBasename, $extension);

    $filepath = trim(($fileRoot === '/' ? '' : $fileRoot) . '/' . $newFilename);
    $mimeType = MimeType::fromFilename($newFilename);

    try {
      // TODO: Transactions
      //DB::beginTransaction();

      $fileArgs = [
        'filepath' => $filepath,
        'filename' => $oldFilename,
        'mimeType' => $mimeType,
      ];

      if (isset($data['description'])) {
        $fileArgs['description'] = $data['description'];
      }

      if (isset($data['private'])) {
        $fileArgs['private'] = $data['private'];
      } else {
        $fileArgs['private'] = false;
      }

      $createdFile = Files::create($fileArgs);
      $file->moveTo($this->config->fs->uploadsPath . $filepath);

      //DB::commit();

      HttpUtils::prepareJsonResponse($response, $createdFile->getData());
    } catch (\Exception $e) {
      //DB::rollback();

      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', $e->getMessage());
    }

    return $response->withStatus(200);
  }

  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $parsedBody = $request->getParsedBody();

    $file = Files::getOneById($args['itemId']);
    $file->update($parsedBody['data']);

    HttpUtils::prepareJsonResponse($response, $file->getData());

    return $response;
  }

  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $id = $args['itemId'];
    try {
      $this->fileService->deleteById($id);

      return $response->withStatus(200);
    } catch (\Exception $e) {
      return $response->withStatus(404);
    }
  }
}
