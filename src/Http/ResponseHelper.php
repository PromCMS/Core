<?php

namespace PromCMS\Core\Http;

use PromCMS\Core\Http\Enums\HttpContentType;
use Psr\Http\Message\ResponseInterface;

class ResponseHelper {
  private ResponseInterface $response;
  private HttpContentType $contentType;

  public static function withServerResponse(
    ResponseInterface &$serverResponse,
    string|array $body,
    int $httpStatus = 200
  ) {
    $contentType = HttpContentType::HTML;

    if (is_array($body)){
      $contentType = HttpContentType::JSON;
    }

    $instance = new self($serverResponse, $contentType);

    $instance->setBody($body);
    $instance->setHttpCode($httpStatus);

    return $instance;
  }

  function __construct(ResponseInterface $serverResponse, HttpContentType $contentType) {
    $this->response = $serverResponse;
    $this->contentType = $contentType;
  }

  public function setBody(string|array $body) {
    if ($this->contentType === HttpContentType::JSON && is_string($body)) {
      throw new \Exception("Response content type is json and you are trying to return string. Please pass array to setBody instead or change content type to HTML");
    }

    $this->response->getBody()->write(is_string($body) ? $body : json_encode($body));

    $this->response->withHeader("Content-Type", $this->contentType->asHeaderValue());
  }

  public function setHttpCode(int $code, string|null $reason = '') {
    $this->response = $this->response->withStatus($code, $reason);
  } 

  public function getResponse() {
    return $this->response;
  }
}