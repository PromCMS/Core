<?php

namespace PromCMS\Core\Utils;

use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Http\Enums\HttpContentType;
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
    $response = $response->withHeader("Content-Type", HttpContentType::JSON->asHeaderValue());

    $response->getBody()->write(
      json_encode([
        'data' => $data,
        'message' => $message,
        'code' => $code,
      ]),
    );

    return $response;
  }

  static function normalizeWhereQueryParam($filterParam)
  {
    $whereQuery = [];
    $PART_SEPARATOR = ';';
    $PIECE_SEPARATOR = '.';
    $stringToExtract = $filterParam;

    // If there is an array instead of string, happens when it was defined like this in url
    if (is_array($filterParam)) {
      $stringToExtract = implode($PART_SEPARATOR, $filterParam);
    }

    // Split by separator and attach each process
    foreach (explode($PART_SEPARATOR, $stringToExtract) as $part) {
      $pieces = explode($PIECE_SEPARATOR, $part);

      if (isset($pieces[0]) && isset($pieces[1]) && isset($pieces[2])) {
        if ($pieces[1] === 'IN') {
          $whereQuery[] = [$pieces[0], 'IN', json_decode("[$pieces[2]]")];
        } else {
          $whereQuery[] = [
            $pieces[0],
            $pieces[1],
            str_replace('/', '\/', $pieces[2]),
          ];
        }
      }
    }

    return [$whereQuery];
  }
}
