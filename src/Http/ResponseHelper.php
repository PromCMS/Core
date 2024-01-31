<?php

namespace PromCMS\Core\Http;

use PromCMS\Core\Database\Paginate;
use Psr\Http\Message\ResponseInterface;

class ResponseHelper
{
  private ResponseInterface $response;
  private ContentType $contentType;

  public static function withServerResponse(
    ResponseInterface &$serverResponse,
    string|array $body,
    int $httpStatus = 200
  ) {
    $contentType = ContentType::HTML;

    if (is_array($body)) {
      $contentType = ContentType::JSON;
    }

    $instance = new self($serverResponse, $contentType);

    $instance->setBody($body);
    $instance->setHttpCode($httpStatus);

    return $instance;
  }

  public static function withServerPagedResponse(
    ResponseInterface &$serverResponse,
    Paginate $body,
    int $httpStatus = 200
  ) {
    $contentType = ContentType::JSON;

    $instance = new self($serverResponse, $contentType);

    $instance->setPagedBody($body);
    $instance->setHttpCode($httpStatus);

    return $instance;
  }

  function __construct(ResponseInterface $serverResponse, ContentType $contentType)
  {
    $this->response = $serverResponse;
    $this->contentType = $contentType;
  }

  public function setPagedBody(array|Paginate $body)
  {
    if ($this->contentType !== ContentType::JSON) {
      throw new \Exception("Response content type must be json for paged body");
    }

    $itemsAsArray = [];
    $items = $body->getItems();
    foreach ($items as $item) {
      if (!is_array($item)) {
        $itemsAsArray[] = $item->toArray();

        continue;
      }

      return $itemsAsArray;
    }

    $responseBody = [
      'data' => $itemsAsArray,
      'current_page' => $body->getCurrentPage(),
      'last_page' => $body->getLastPage(),
      'total' => $body->getTotal(),
    ];

    $this->response->getBody()->write(json_encode($responseBody));

    $this->response->withHeader("Content-Type", $this->contentType->asHeaderValue());

    return $this;
  }

  public function setBody(string|array $body)
  {
    if ($this->contentType === ContentType::JSON && is_string($body)) {
      throw new \Exception("Response content type is json and you are trying to return string. Please pass array to setBody instead or change content type to HTML");
    }

    $this->response->getBody()->write(is_string($body) ? $body : json_encode($body));

    $this->response->withHeader("Content-Type", $this->contentType->asHeaderValue());

    return $this;
  }

  public function setHttpCode(int $code, string|null $reason = '')
  {
    $this->response = $this->response->withStatus($code, $reason);

    return $this;
  }

  public function getResponse()
  {
    return $this->response;
  }
}