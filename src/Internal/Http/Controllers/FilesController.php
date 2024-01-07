<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Filesystem;
use PromCMS\Core\Http\WhereQueryParam;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Entity;
use PromCMS\Core\Session;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\UploadedFile;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Utils\HttpUtils;

use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\ImageService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class FilesController
{
  private $container;
  private PromConfig $promConfig;
  private Filesystem $fs;
  private FileService $fileService;
  private ImageService $imageService;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $this->fs = $container->get(Filesystem::class);
    $this->fileService = $container->get(FileService::class);
    $this->imageService = $container->get(ImageService::class);
    $this->promConfig = $container->get(PromConfig::class);
  }

  public function getInfo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $entity = $request->getAttribute(Entity::class);
    HttpUtils::prepareJsonResponse($response, $this->promConfig->getEntityAsArray($entity->tableName));

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
        $this->fileService->getById($args['itemId'])->toArray(),
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
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $limit = intval($queryParams['limit'] ?? 15);
    $where = null;

    if (isset($queryParams['where'])) {
      $where = new WhereQueryParam($queryParams['where']);
    }

    if (!empty($queryParams['path'])) {
      $result = $this->fileService->getManyInDirectoryPaged($queryParams['path'], $page, $limit, $where);
    } else {
      $result = $this->fileService->getManyPaged($page, $limit, $where);
    }

    return ResponseHelper::withServerPagedResponse($response, $result)
      ->getResponse();
  }

  public function getFile(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $id = $args['itemId'];
    $queryParams = $request->getQueryParams();

    try {
      $userId = $this->container->get(Session::class)->get('user_id', false);
      $fileInfo = $this->fileService->getById($id);
      $responseMimeType = $this->fs->withUploads()->mimeType($fileInfo->filepath);

      if ($fileInfo->private && !$userId) {
        return $response->withStatus(401);
      }

      if (preg_match('/image\/.*/', $fileInfo->getMimeType())) {
        $imageResource = $this->imageService->getProcessed(
          $fileInfo,
          $queryParams,
        );
        $stream = new Stream($imageResource['resource']);
        $responseMimeType = mime_content_type($imageResource['resource']);
      } else {
        $stream = $this->fileService->getStream($fileInfo);
      }

      return $response
        ->withHeader('Content-Type', $responseMimeType)
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

    /**
     * @var UploadedFile
     */
    $file = $files['file'];

    // If theres an error on upload
    if ($file->getError() !== UPLOAD_ERR_OK) {
      switch ($file->getError()) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          return $response->withStatus(413);
        case UPLOAD_ERR_EXTENSION:
          return $response->withStatus(415);
        default:
          return $response->withStatus(500);
      }
    }

    try {
      $data = $queryParams ? $queryParams : [];
      $createdFile = $this->fileService->create($file, $data);

      HttpUtils::prepareJsonResponse($response, $createdFile->toArray());
    } catch (\Exception $e) {
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
    $updatedFile = $this->fileService->updateById($args['itemId'], $parsedBody['data']);

    HttpUtils::prepareJsonResponse($response, $updatedFile->toArray());

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