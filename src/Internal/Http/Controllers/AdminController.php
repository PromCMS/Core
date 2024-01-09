<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use DI\Container;
use PromCMS\Core\Http\Routing\AsRedirectRoute;
use PromCMS\Core\Http\Routing\AsRoute;
use PromCMS\Core\PromConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Filesystem\Path;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class AdminController
{
  private PromConfig $promConfig;

  public function __construct(private Container $container)
  {
  }

  #[AsRoute('GET', '/admin[/{routePiece:.*}]')]
  public function getInfo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
  {
    $routePiece = $args['routePiece'] ?? "";
    $routePiece = str_replace("..", "", $routePiece);

    $adminPath = Path::join($this->container->get('app.root'), 'public', 'admin');

    if (str_ends_with($routePiece, ".css") || str_ends_with($routePiece, ".js")) {
      $requiredPath = "$adminPath/$routePiece";

      if (file_exists($requiredPath)) {
        $content = file_get_contents("$adminPath/$routePiece", 'r');
        $response = $response->withHeader('Content-type', str_ends_with($routePiece, ".js") ? "application/javascript" : "text/css");
      } else {
        return $response->withStatus(404);
      }
    } else {
      $content = file_get_contents($adminPath . '/index.html', 'r');
    }

    $response->getBody()->write($content);

    return $response;
  }

  // TODO: Better approach through apache config?
  #[AsRedirectRoute('/public/admin', '/admin/')]
  public function adminFromPublic()
  {
  }

  // TODO: Better approach through apache config?
  #[AsRedirectRoute('/public/admin/', '/admin/')]
  public function adminFromPublicWithTrailingSlash()
  {
  }
}
