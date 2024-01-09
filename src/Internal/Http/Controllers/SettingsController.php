<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Utils\HttpUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class SettingsController
{
  #[AsApiRoute('GET', '/settings')]
  public function get(
    ServerRequestInterface $request,
    ResponseInterface $response,
    PromConfig $promConfig,
    $args
  ): ResponseInterface {
    HttpUtils::prepareJsonResponse($response, [
      'php' => [
        'upload_max_filesize' => ini_get('upload_max_filesize')
      ],
      'application' => [
        'languages' => $promConfig->getProject()->languages
      ]
    ]);

    return $response;
  }
}
