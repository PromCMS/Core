<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use DI\Container;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemException;
use PromCMS\Core\Config;
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

  public function __construct(Container $container)
  {
    $this->fs = $container->get(Filesystem::class);
    $this->config = $container->get(Config::class);
  }

  private function listingHasContents(DirectoryListing $listing)
  {
    $hasItems = false;

    // TODO: Why have foreach?
    foreach ($listing as $item) {
      $hasItems = true;
      break;
    }

    return $hasItems;
  }

  #[
    AsApiRoute('GET', '/folders'),
    WithMiddleware(UserLoggedInMiddleware::class)]
  public function get(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $params = $request->getQueryParams();
    $dirname = $params['path'];

    try {
      $listing = $this->fs->withUploads()->listContents($dirname, false);
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
    $dirname = $data['path'];

    try {
      if ($this->fs->withUploads()->directoryExists($dirname)) {
        return $response->withStatus(409);
      }

      $this->fs->withUploads()->createDirectory($dirname);

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
    $dirname = $data['path'];

    try {
      $hasItems = $this->listingHasContents(
        $this->fs->withUploads()->listContents($dirname, false),
      );

      if ($hasItems) {
        return $response->withStatus(400);
      }

      $this->fs->withUploads()->deleteDirectory($dirname);

      return $response->withStatus(200);
    } catch (FilesystemException $exception) {
      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', $exception->getMessage());
    }
  }
}
