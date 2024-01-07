<?php

namespace PromCMS\Core\Http\Routes;

use PromCMS\Core\Path;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FrontRoutes implements CoreRoutes
{
  function __construct($container)
  {
  }

  function attachAllHandlers($router)
  {
    // TODO: Better approach through apache config?
    $router->redirect("/public/admin", "/admin/");
    $router->redirect("/public/admin/", "/admin/");
    $router->get('/admin[/{routePiece:.*}]', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
      $routePiece = $args['routePiece'] ?? "";
      $routePiece = str_replace("..", "", $routePiece);

      $adminPath = Path::join($this->get('app.root'), 'public', 'admin');

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
    });
  }
}
