<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Internal\Http\Middleware\EntityPermissionMiddleware;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\AsRouteGroup;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Internal\Http\Middleware\ModelMiddleware;
use PromCMS\Core\Services\LocalizationService;
use DI\Container;
use PromCMS\Core\Utils\HttpUtils;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
#[AsRouteGroup(pathnamePrefix: '/entry-types/{modelId:generalTranslations|prom__general_translations}')]
class LocalizationController
{
  private Container $container;
  private LocalizationService $localizationService;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $this->localizationService = $this->container->get(LocalizationService::class);
  }

  #[
    AsApiRoute('POST', '/items'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
  ]
  function updateOne(
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

  #[
    AsApiRoute('DELETE', '/items/delete'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  function deleteOne(
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

  #[
    AsApiRoute('GET', '/items'),
    WithMiddleware(ModelMiddleware::class)
  ]
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
  #[AsApiRoute('GET', '/locales/{lang}.json')]
  function getLocalization(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $lang = $request->getAttribute('lang');
    $locales = $this->localizationService->getTranslations($lang);

    return ResponseHelper::withServerResponse($response, $locales)->getResponse();
  }
}
