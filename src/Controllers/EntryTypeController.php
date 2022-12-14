<?php

namespace PromCMS\Core\Controllers;

use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\Config;
use PromCMS\Core\Config\i18n as i18nConfig;
use PromCMS\Core\HttpUtils;
use PromCMS\Core\Services\EntryTypeService;
use PromCMS\Core\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EntryTypeController
{
  protected $currentUser;
  protected i18nConfig $languageConfig;

  private function getCurrentLanguage($request, $args)
  {
    $queryParams = $request->getQueryParams();
    $nextLanguage = null;
    $supportedLanguages = $this->languageConfig->languages;

    if (
      isset($queryParams['lang']) &&
      in_array($queryParams['lang'], $supportedLanguages)
    ) {
      $nextLanguage = $queryParams['lang'];
    }

    if (isset($args['language'])) {
      $nextLanguage = $args['language'];
    }

    return $nextLanguage;
  }

  public function getInfo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $instance = $request->getAttribute('model-instance');

    HttpUtils::prepareJsonResponse($response, $instance->getSummary());

    return $response;
  }

  public function __construct(Container $container)
  {
    $this->currentUser = $container->get('session')->get('user', false);
    $this->languageConfig = $container->get(Config::class)->i18n;
  }

  public function create(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ): ResponseInterface {
    $modelInstance = $request->getAttribute('model-instance');
    $service = new EntryTypeService(
      $modelInstance,
      $this->getCurrentLanguage($request, $args),
    );
    $parsedBody = $request->getParsedBody();

    try {
      if ($modelInstance->getSummary()->ownable && $this->currentUser) {
        $parsedBody['data']['created_by'] = $this->currentUser->id;
      }

      $item = $service->create($parsedBody['data']);

      HttpUtils::prepareJsonResponse($response, $item->getData());

      return $response;
    } catch (\Exception $ex) {
      $response = $response->withHeader(
        'Content-Description',
        $ex->getMessage(),
      );

      if ($ex instanceof EntityDuplicateException) {
        HttpUtils::handleDuplicateEntriesError($response, $ex);

        return $response->withStatus(400);
      } else {
        return $response->withStatus(500);
      }
    }
  }

  public function getOne(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $modelInstance = $request->getAttribute('model-instance');
    $service = new EntryTypeService(
      $modelInstance,
      $this->getCurrentLanguage($request, $args),
    );

    try {
      HttpUtils::prepareJsonResponse(
        $response,
        $service
          ->getOne(
            array_merge(
              [['id', '=', intval($args['itemId'])]],
              $request->getAttribute('permission-only-own', false) === true
                ? Utils::getOnlyOwnersOrEditorsFilter($this->currentUser->id, $modelInstance)
                : [],
            ),
          )
          ->getData(),
      );

      return $response;
    } catch (\Exception $error) {
      return $response
        ->withStatus(404)
        ->withHeader('Content-Description', $error->getMessage());
    }
  }

  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ): ResponseInterface {
    $modelInstance = $request->getAttribute('model-instance');
    $service = new EntryTypeService(
      $modelInstance,
      $this->getCurrentLanguage($request, $args),
    );
    $queryParams = $request->getQueryParams();
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $limit = intval($queryParams['limit'] ?? 15);
    $where = [];

    // If current user can view this content
    if ($request->getAttribute('permission-only-own', false) === true) {
      $filter = Utils::getOnlyOwnersOrEditorsFilter($this->currentUser->id, $modelInstance);
      $where = $filter;
    }

    $response
      ->getBody()
      ->write(json_encode($service->getMany($where, $page, $limit)));

    return $response;
  }

  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $modelInstance = $request->getAttribute('model-instance');
    $service = new EntryTypeService(
      $modelInstance,
      $this->getCurrentLanguage($request, $args),
    );
    $parsedBody = $request->getParsedBody();

    try {
      $where = [['id', '=', intval($args['itemId'])]];

      if ($request->getAttribute('permission-only-own', false) === true) {
        $where = array_merge(
          $where,
          Utils::getOnlyOwnersOrEditorsFilter($this->currentUser->id, $modelInstance),
        );
      }

      if ($modelInstance->getSummary()->ownable && $this->currentUser) {
        $parsedBody['data']['updated_by'] = $this->currentUser->id;
      }

      $item = $service->update($where, $parsedBody['data']);

      HttpUtils::prepareJsonResponse($response, $item->getData());

      return $response;
    } catch (\Exception $ex) {
      $response = $response->withHeader(
        'Content-Description',
        $ex->getMessage(),
      );

      if ($ex instanceof EntityDuplicateException) {
        HttpUtils::handleDuplicateEntriesError($response, $ex);
        return $response->withStatus(400);
      } elseif ($ex instanceof EntityNotFoundException) {
        return $response->withStatus(404);
      } else {
        return $response->withStatus(500);
      }
    }
  }

  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $modelInstance = $request->getAttribute('model-instance');
    $service = new EntryTypeService($modelInstance);

    $where = [['id', '=', intval($args['itemId'])]];

    if ($request->getAttribute('permission-only-own', false) === true) {
      $where = array_merge(
        $where,
        Utils::getOnlyOwnersOrEditorsFilter($this->currentUser->id, $modelInstance),
      );
    }

    if (!$service->delete($where)) {
      HttpUtils::prepareJsonResponse($response, [], 'Failed to delete');

      return $response
        ->withStatus(500)
        ->withHeader('Content-Description', 'Failed to delete');
    }

    HttpUtils::prepareJsonResponse($response, [], 'Item deleted');

    return $response;
  }

  public function swapTwo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $classInstance = $request->getAttribute('model-instance');
    $parsedBody = $request->getParsedBody();
    $data = $parsedBody['data'];

    if (
      !$classInstance->getSummary()->hasOrdering ||
      !isset($data['fromId']) ||
      !isset($data['toId']) ||
      $data['fromId'] === $data['toId']
    ) {
      return $response->withStatus(400);
    }

    // TODO: add transactions
    try {
      $ownableQueryFilter =
        $request->getAttribute('permission-only-own', false) === true
        ? Utils::getOnlyOwnersOrEditorsFilter($this->currentUser->id, $classInstance)
        : [];

      $fromEntry = $classInstance
        ->where(
          array_merge(
            ['id', '=', intval($data['fromId'])],
            $ownableQueryFilter,
          ),
        )
        ->getOne();

      $toEntry = $classInstance
        ->where(
          array_merge(['id', '=', intval($data['toId'])], $ownableQueryFilter),
        )
        ->getOne();

      // just make copy of data with just 'order' values which we will be saving to db
      $fromEntryData = ['order' => $fromEntry->order];
      $toEntryData = ['order' => $toEntry->order];

      $savedOrderId = $toEntryData['order'];
      $toEntryData['order'] = $fromEntryData['order'];
      $fromEntryData['order'] = $savedOrderId;

      if ($classInstance->getSummary()->ownable && $this->currentUser) {
        $fromEntryData['updated_by'] = $this->currentUser->id;
        $toEntryData['updated_by'] = $this->currentUser->id;
      }

      $fromEntry->update($fromEntryData);
      $toEntry->update($toEntryData);

      HttpUtils::prepareJsonResponse($response, [], '', 'success');
    } catch (\Exception $e) {
      return $response
        ->withStatus(500)
        ->withHeader('x-custom-message', $e->getMessage())
        ->withHeader('x-custom-trace', $e->getTraceAsString());
    }

    return $response;
  }
}
