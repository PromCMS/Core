<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Filesystem;
use PromCMS\Core\Internal\Http\Middleware\ModelMiddleware;
use PromCMS\Core\Internal\Http\Middleware\EntityPermissionMiddleware;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\AsRouteGroup;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Http\WhereQueryParam;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Session;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use GuzzleHttp\Psr7\Stream;
use Slim\Psr7\UploadedFile;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Utils\HttpUtils;

use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\ImageService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
#[AsRouteGroup('/library/{modelId:files}')]
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

  #[
    AsApiRoute('GET', '/items/{itemId}'),
    WithMiddleware(ModelMiddleware::class),
  ]
  public function getOne(
    ServerRequestInterface $request,
    ResponseInterface $response,
    Session $session
  ): ResponseInterface {
    $itemId = $request->getAttribute('itemId');

    try {
      $userId = $session->get('user_id', false);
      $fileInfo = $this->fileService->getById($itemId);
      $responseMimeType = $this->fs->withUploads()->mimeType($fileInfo->getFilepath());

      if ($fileInfo->getPrivate() && !$userId) {
        return $response->withStatus(401);
      }

      // Sometimes user will request information as json, then just return it so
      if ($request->getHeader('accept')[0] === 'application/json') {
        if (!$userId) {
          return $response->withStatus(401);
        }


        HttpUtils::prepareJsonResponse(
          $response,
          $fileInfo->toArray(),
        );

        return $response;
      }

      $queryParams = $request->getQueryParams();
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

  #[
    AsApiRoute('PATCH', '/items/{itemId}/move'),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(UserLoggedInMiddleware::class)
  ]
  public function moveOne(
    ServerRequestInterface $request,
    ResponseInterface $response,
    Session $session,
    EntityManager $em,
    $itemId
  ): ResponseInterface {
    $queryParams = $request->getQueryParams();
    $moveTo = $queryParams['to'] ?? '/';

    try {
      $fileInfo = $this->fileService->getById($itemId);
      $fileInfo->moveTo($moveTo);
      $em->flush();

      return $response->withStatus(200);
    } catch (\Exception $e) {
      if ($e instanceof EntityNotFoundException) {
        return $response->withStatus(404);
      }

      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', $e->getMessage());
    }
  }

  #[
    AsApiRoute('GET', '/items'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
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

  #[
    AsApiRoute('POST', '/items/create'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
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

  #[
    AsApiRoute('PATCH', '/items/{itemId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $itemId = $request->getAttribute('itemId');
    $parsedBody = $request->getParsedBody();
    $data = $parsedBody['data'];

    $updatedFile = $this->fileService->updateById($itemId, $data);

    HttpUtils::prepareJsonResponse($response, $updatedFile->toArray());

    return $response;
  }

  #[
    AsApiRoute('DELETE', '/items/{itemId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $itemId = $request->getAttribute('itemId');

    try {
      $this->fileService->deleteById($itemId);

      return $response->withStatus(200);
    } catch (\Exception $e) {
      return $response->withStatus(404)->withHeader('Content-Description', $e->getMessage());
    }
  }
}
