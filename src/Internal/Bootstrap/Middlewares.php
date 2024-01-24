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

      // First priority if 'lang' query parameter
      if (!empty($requestQueryParams["lang"])) {
        $languageFromQuery = $requestQueryParams["lang"];

        $localizationService->setCurrentLanguage($languageFromQuery, false);
      }
      // If not defined in query param then check for url
      else if (preg_match("/^\/($possibleLanguagesAsJoinedString)($|\/\.*)/", $path)) {
        $firstSegmentAsLanguage = explode('/', $path)[1];

        $localizationService->setCurrentLanguage($firstSegmentAsLanguage);
      }
      // fallback to header value
      else if (!empty($acceptLanguages = $request->getHeader('accept-language'))) {
        $prefLocalesFromHeader = array_reduce(
          explode(',', $acceptLanguages[0]),
          function ($res, $el) {
            list($l, $q) = array_merge(explode(';q=', $el), [1]);
            $res[explode('-', $l)[0]] = (float) $q;
            return $res;
          },
          []
        );
        arsort($prefLocalesFromHeader);

        $localizationService->setCurrentLanguage(array_keys($prefLocalesFromHeader)[0], false);
      }

      return $handler->handle($request->withAttribute('lang', $localizationService->getCurrentLanguage()));
    };

    $app->add($localizationMiddleware);
  }
}
