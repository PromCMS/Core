<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Services\LocalizationService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


class Middlewares implements AppModuleInterface
{
  public function run($app, $container)
  {
    $localizationMiddleware = function (Request $request, RequestHandler $handler) use ($container) {
      $path = $request->getUri()->getPath();  
      $localizationService = $container->get(LocalizationService::class);
      $possibleLanguages = $localizationService->getSupportedLanguages();
      $possibleLanguagesAsJoinedString = implode('|',$possibleLanguages);

      if (preg_match("/^\/($possibleLanguagesAsJoinedString)($|\/\.*)/", $path)) {
        $firstSegmentAsLanguage = explode('/', $path)[1];

        $localizationService->setCurrentLanguage($firstSegmentAsLanguage);
      }

      return $handler->handle($request);
    };
  
    $app->add($localizationMiddleware);
  }
}
