<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use DI\Container;
use League\Flysystem\FilesystemException;
use PromCMS\Core\Config;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Models\File;
use PromCMS\Core\Filesystem;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\AsRouteGroup;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Utils\HttpUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
#[AsRouteGroup('/library')]
class FoldersController
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

  private function hasDirContents(string $basename): bool
  {
    if (!$this->fs->withUploads()->directoryExists($basename)) {
      return false;
    }

    $files = $this->em
      ->createQueryBuilder()
      ->select('f')
      ->from(File::class, 'f')
      ->where('f.filepath LIKE :filepath%')
      ->setParameter(':filepath', $basename)
      ->setMaxResults(1)
      ->getQuery()
      ->getOneOrNullResult();

    return count($files) > 0;
  }

  #[
    AsApiRoute('GET', '/folders'),
    WithMiddleware(UserLoggedInMiddleware::class)]
  public function get(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $params = $request->getQueryParams();
    $basename = $params['path'];

    try {
      $listing = $this->fs->withUploads()->listContents($basename, false);
      $folders = [];

      /** @var \League\Flysystem\StorageAttributes $item */
      foreach ($listing as $item) {
        $path = $item->path();

        if ($item instanceof \League\Flysystem\DirectoryAttributes) {
          $folders[] = basename($path);
        }
      }

      HttpUtils::prepareJsonResponse($response, $folders);

      return $response->withStatus(200);
    } catch (FilesystemException $exception) {
      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', $exception->getMessage());
    }
  }

  #[
    AsApiRoute('POST', '/folders'),
    WithMiddleware(UserLoggedInMiddleware::class)]
  public function create(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $parsedBody = $request->getParsedBody();
    $data = $parsedBody['data'];
    $basename = $data['path'];

    try {
      if ($this->fs->withUploads()->directoryExists($basename)) {
        return $response->withStatus(409);
      }

      $this->fs->withUploads()->createDirectory($basename);

      return $response->withStatus(200);
    } catch (FilesystemException $exception) {
      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', $exception->getMessage());
    }
  }

  #[
    AsApiRoute('DELETE', '/folders'),
    WithMiddleware(UserLoggedInMiddleware::class)]
  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $data = $request->getQueryParams();
    $basename = $data['path'];

    try {
      $hasItems = $this->hasDirContents($basename);

      if ($hasItems) {
        return $response->withStatus(400);
      }

      $this->fs->withUploads()->deleteDirectory($basename);

      return $response->withStatus(200);
    } catch (FilesystemException $exception) {
      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', $exception->getMessage());
    }
  }
}
