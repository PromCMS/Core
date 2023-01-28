<?php

namespace PromCMS\Core\Controllers\Traits\Model;

use PromCMS\Core\HttpUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait Info
{

  public function getInfo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $instance = $request->getAttribute('model-instance');

    HttpUtils::prepareJsonResponse($response, $instance->getSummary());

    return $response;
  }
}
