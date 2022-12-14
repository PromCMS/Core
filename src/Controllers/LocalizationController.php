<?php

namespace PromCMS\Core\Controllers;

use PromCMS\Core\Services\LocalizationService;
use DI\Container;
use PromCMS\Core\HttpUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LocalizationController
{
  private LocalizationService $localizationService;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $this->localizationService = $this->container->get(LocalizationService::class);
  }

  function updateTranslation(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $body = $request->getParsedBody();
    if (
      !isset($body['lang']) ||
      !isset($body['key']) ||
      !isset($body['value'])
    ) {
      return $response->withStatus(400);
    }

    $lang = $body['lang'];
    $key = $body['key'];
    $value = $body['value'];

    if (!$key || !$lang) {
      return $response->withStatus(400);
    }

    $this->localizationService->updateTranslation($lang, $key, $value);

    return $response;
  }

  function delete(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $body = $request->getParsedBody();

    if (!isset($body['key'])) {
      return $response->withStatus(400);
    }
    $key = $body['key'];

    $this->localizationService->deleteTranslationKey($key);

    return $response;
  }

  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $queryParams = $request->getQueryParams();
    if (
      !isset($queryParams['lang'])
    ) {
      return $response->withStatus(400);
    }
    $lang = $queryParams['lang'];

    $translations = $this->localizationService->getTranslations($lang, true);

    HttpUtils::prepareJsonResponse(
      $response,
      $translations,
    );

    return $response;
  }

  /**
   * Gets localization for defined language
   */
  function getLocalization(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    if (!isset($args['lang'])) {
      return $response->withStatus(400);
    }
    $locales = $this->localizationService->getTranslations($args['lang']);

    $response->getBody()->write(json_encode($locales));

    return $response;
  }
}
