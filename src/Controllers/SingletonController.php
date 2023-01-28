<?php

namespace PromCMS\Core\Controllers;

use DI\Container;
use PromCMS\Core\Config;
use PromCMS\Core\HttpUtils;
use PromCMS\Core\Services\SingletonService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SingletonController
{
  static string $ITEM_ARGUMENT_NAME = 'modelId';

  use \PromCMS\Core\Controllers\Traits\Model\I18n, \PromCMS\Core\Controllers\Traits\Model\Info;

  protected $currentUser;

  public function __construct(Container $container)
  {
    $this->currentUser = $container->get('session')->get('user', false);
    $this->languageConfig = $container->get(Config::class)->i18n;
  }

  public function getOne(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $modelInstance = $request->getAttribute('model-instance');
    $service = new SingletonService(
      $modelInstance,
      $this->getCurrentLanguage($request, $args),
    );

    try {
      HttpUtils::prepareJsonResponse(
        $response,
        $service
          ->getOne([])
          ->getData(),
      );

      return $response;
    } catch (\Exception $error) {
      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', $error->getMessage());
    }
  }

  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $modelInstance = $request->getAttribute('model-instance');
    $service = new SingletonService(
      $modelInstance,
      $this->getCurrentLanguage($request, $args),
    );
    $parsedBody = $request->getParsedBody();

    try {
      if ($modelInstance->getSummary()->ownable && $this->currentUser) {
        $parsedBody['data']['updated_by'] = $this->currentUser->id;
      }

      $item = $service->update([], $parsedBody['data']);

      HttpUtils::prepareJsonResponse($response, $item->getData());

      return $response;
    } catch (\Exception $ex) {
      $response = $response->withHeader(
        'Content-Description',
        $ex->getMessage(),
      );

      return $response->withStatus(500);
    }
  }

  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $modelInstance = $request->getAttribute('model-instance');
    $service = new SingletonService($modelInstance);
    $where = [];

    if (!$service->clear($where)) {
      HttpUtils::prepareJsonResponse($response, [], 'Failed to delete');

      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', 'Failed to delete');
    }

    HttpUtils::prepareJsonResponse($response, [], 'Singleton cleared');

    return $response;
  }
}
