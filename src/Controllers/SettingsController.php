<?php

namespace PromCMS\Core\Controllers;

use PromCMS\Core\Config;
use PromCMS\Core\Utils\HttpUtils;;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SettingsController
{
  private Config $config;

  // Let it be injected by psr
  public function __construct(Config $config)
  {
    $this->config = $config;
  }

  public function get(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ): ResponseInterface {
    HttpUtils::prepareJsonResponse($response, [
      'i18n' => $this->config->i18n,
      'app' => array_diff_key((array) $this->config->app, [
        'root' => null,
      ]),
      'php' => [
        'upload_max_filesize' => ini_get('upload_max_filesize')
      ]
    ]);

    return $response;
  }
}
