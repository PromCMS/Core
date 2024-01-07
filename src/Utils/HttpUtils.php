<?php

namespace PromCMS\Core\Utils;

use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Http;
use Psr\Http\Message\ResponseInterface;

class HttpUtils
{
  static function handleDuplicateEntriesError(
    $response,
    EntityDuplicateException $exception
  ) {
    static::prepareJsonResponse(
      $response,
      $exception->getFailedFields(),
      'Duplicate entries',
      intval($exception->getCode()),
    );
  }

  static function prepareJsonResponse(
    ResponseInterface &$response,
    array $data,
    string $message = '',
    $code = false
  ) {
    $response = $response->withHeader("Content-Type", Http\ContentType::JSON->asHeaderValue());

    $response->getBody()->write(
      json_encode([
        'data' => $data,
        'message' => $message,
        'code' => $code,
      ]),
    );

    return $response;
  }
}
