<?php

namespace PromCMS\Core\Http\Routes;

use PromCMS\Core\Config;
use PromCMS\Core\Path;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FrontRoutes implements CoreRoutes
{
  private Config $config;

  public function __construct($container)
  {
    $this->config = $container->get(Config::class);
  }

  function attachAllHandlers($router)
  {
    $router->get('/admin[/{routePiece:.*}]', function (
      ServerRequestInterface $request,
      ResponseInterface $response,
      $args
    ) {
      $adminPath = Path::join($this->config->app->root, 'public', 'admin');
      $content = file_get_contents($adminPath . '/index.html', 'r');
      $response->getBody()->write($content);

      return $response;
    });
  }
}
