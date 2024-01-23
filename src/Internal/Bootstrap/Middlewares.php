<?php

namespace PromCMS\Core\Internal\Bootstrap;

use DI\Container;
use PromCMS\Core\Services\LocalizationService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class Middlewares implements AppModuleInterface
{
  public function run($app, Container $container)
  {
    $localizationMiddleware = function (Request $request, RequestHandler $handler) use ($container) {
      $requestUri = $request->getUri();
      $path = $requestUri->getPath();
      $requestQueryParams = $request->getQueryParams();
      $localizationService = $container->get(LocalizationService::class);
      $possibleLanguages = $localizationService->getSupportedLanguages();
      $possibleLanguagesAsJoinedString = implode('|', $possibleLanguages);

      if (preg_match("/^\/($possibleLanguagesAsJoinedString)($|\/\.*)/", $path)) {
        $firstSegmentAsLanguage = explode('/', $path)[1];

        $localizationService->setCurrentLanguage($firstSegmentAsLanguage);
      } else if (!empty($acceptLanguages = $request->getHeader('accept-language'))) {
        $languageFromRequest = $acceptLanguages[0];

        $localizationService->setCurrentLanguage($languageFromRequest, false);
      } else if (!empty($requestQueryParams["lang"])) {
        $languageFromQuery = $requestQueryParams["lang"];

        $localizationService->setCurrentLanguage($languageFromQuery, false);
      }

      $request->withAttribute('lang', $localizationService->getCurrentLanguage());
      return $handler->handle($request);
    };

    $app->add($localizationMiddleware);
  }
}
