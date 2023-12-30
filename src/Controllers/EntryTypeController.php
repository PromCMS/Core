<?php

namespace PromCMS\Core\Controllers;

use PromCMS\Core\Models\FileQuery;
use PromCMS\Core\Models\User;
use PromCMS\Core\Session;
use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\Config;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Utils\HttpUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Propel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use PromCMS\Core\Config\i18n as I18nConfig;

/**
 * Take care of normal models and singletons!
 */
class EntryTypeController
{
  protected $currentUser;

  protected I18nConfig $languageConfig;

  public function __construct(Container $container)
  {
    $this->currentUser = $container->get(Session::class)->get('user', false);
    $this->languageConfig = $container->get(Config::class)->i18n;
  }

  private function getCurrentLanguage($request, $args)
  {
    $queryParams = $request->getQueryParams();
    $nextLanguage = $this->languageConfig->default;
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

  private function isLocalizedModel(TableMap $tableMap)
  {
    return isset($tableMap->getBehaviors()["i18n"]);
  }

  private function isSortableModel(TableMap $tableMap)
  {
    return isset($tableMap->getBehaviors()["sortable"]);
  }

  private function isSharableModel(TableMap $tableMap)
  {
    // TODO
    return false;
  }

  private function isModelSingleton(TableMap $tableMap)
  {
    return str_starts_with($tableMap::TABLE_NAME, "singleton_");
  }

  private function filterQueryOnlyToOwners(TableMap $modelTableMap, User $currentUser, &$query)
  {
    $query->filterBy("created_by", $currentUser->getId());

    if ($this->isSharableModel($modelTableMap)) {
      $query
        ->_or()
        ->filterBy("coeditors.user_id", $currentUser->getId());

    }
  }

  public function create(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ): ResponseInterface {
    $modelAsString = $request->getAttribute('model')->entry;
    $modelTableMap = new($request->getAttribute('model')->map)();
    $modelInstance = new $modelAsString();

    if ($this->isLocalizedModel($modelTableMap)) {
      $modelInstance->setLocale($this->getCurrentLanguage($request, $args));
    }

    $parsedBody = $request->getParsedBody();

    try {
      if ($this->isSharableModel($modelTableMap) && $this->currentUser) {
        $parsedBody['data']['created_by'] = $this->currentUser->getId();
      }

      $modelInstance->fromArray($parsedBody['data']);
      $modelInstance->save();

      HttpUtils::prepareJsonResponse($response, $modelInstance->toArray(TableMap::TYPE_CAMELNAME));

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
    $modelTableMap = new($request->getAttribute('model')->map)();
    $query = ($request->getAttribute('model')->query)::create();

    if ($this->isLocalizedModel($modelTableMap)) {
      $query->joinWithI18n($this->getCurrentLanguage($request, $args));
    }

    if (!$this->isModelSingleton($modelTableMap)) {
      $query->filterById($args['itemId']);

      // TODO
      if ($request->getAttribute('permission-only-own', false) === true) {
        $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
      }
    }

    try {
      $foundItem = $query->findOne();

      if (!$foundItem) {
        throw new EntityNotFoundException();
      }

      HttpUtils::prepareJsonResponse(
        $response,
        $foundItem->toArray(TableMap::TYPE_CAMELNAME)
      );

      return $response;
    } catch (\Exception | EntityNotFoundException $error) {
      // If it does not exist then create it
      if ($error instanceof EntityNotFoundException) {
        return $this->create($request, $response, $args);
      }

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
    $modelTableMap = new($request->getAttribute('model')->map)();
    $query = ($request->getAttribute('model')->query)::create();

    if ($this->isModelSingleton($modelTableMap)) {
      return $response->withStatus(404);
    }

    if ($this->isLocalizedModel($modelTableMap)) {
      $query->joinWithI18n($this->getCurrentLanguage($request, $args));
    }

    $queryParams = $request->getQueryParams();
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $limit = intval($queryParams['limit'] ?? 15);

    // If current user can view this content
    if ($request->getAttribute('permission-only-own', false) === true) {
      $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
    }

    // TODO - make it more dynamic
    if (isset($queryParams['orderBy_created_at'])) {
      $query->orderBy("created_at", $queryParams['orderBy_created_at']);
    }

    return ResponseHelper::withServerPagedResponse($response, $query->paginate($page, $limit))->getResponse();
  }

  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $modelTableMap = new($request->getAttribute('model')->map)();
    $query = ($request->getAttribute('model')->query)::create();

    if ($this->isLocalizedModel($modelTableMap)) {
      $query->joinWithI18n($this->getCurrentLanguage($request, $args));
    }

    $parsedBody = $request->getParsedBody();

    if (!$this->isModelSingleton($modelTableMap)) {
      $query->filterById($args['itemId']);

      // TODO: should singletons be with permissions?
      if ($request->getAttribute('permission-only-own', false) === true) {
        $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
      }
    }

    if ($this->isSharableModel($modelTableMap) && $this->currentUser) {
      $parsedBody['data']['updated_by'] = $this->currentUser->getId();
    }

    try {
      $foundItem = $query->findOne();

      if (!$foundItem) {
        throw new EntityNotFoundException();
      }

      $foundItem->fromArray($parsedBody['data']);
      $foundItem->save();

      HttpUtils::prepareJsonResponse($response, $foundItem->toArray(TableMap::TYPE_CAMELNAME));

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
    $modelTableMap = new($request->getAttribute('model')->map)();
    $query = ($request->getAttribute('model')->query)::create();

    if ($this->isLocalizedModel($modelTableMap)) {
      $query->joinWithI18n($this->getCurrentLanguage($request, $args));
    }

    if (!$this->isModelSingleton($modelTableMap)) {
      $query->filterById($args['itemId']);

      // TODO
      if ($request->getAttribute('permission-only-own', false) === true) {
        $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
      }
    }

    if (!$query->delete()) {
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
    $modelTableMap = new($request->getAttribute('model')->map)();
    $parsedBody = $request->getParsedBody();
    $data = $parsedBody['data'];
    $query = ($request->getAttribute('model')->query)::create();

    if ($this->isModelSingleton($modelTableMap)) {
      return $response->withStatus(404);
    }

    if (
      !$this->isSortableModel($modelTableMap) ||
      empty($fromId = $data['fromId']) ||
      empty($toId = $data['toId']) ||
      $data['fromId'] === $data['toId']
    ) {
      return $response->withStatus(400);
    }

    $fromId = intval($fromId);
    $toId = intval($toId);

    if ($request->getAttribute('permission-only-own', false) === true) {
      $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
    }

    $results = FileQuery::create()->findById([$fromId, $toId], Criteria::IN);

    if ($results->count() !== 2) {
      return $response->withStatus(400);
    }

    $transactionConnection = Propel::getWriteConnection(($modelTableMap)::TABLE_NAME);

    foreach ($results as $result) {
      if ($result->getId() === $fromId) {
        $fromEntry = $result;
      } else {
        $toEntry = $result;
      }
    }

    try {
      if ($this->isSharableModel($modelTableMap) && $this->currentUser) {
        $fromEntry->setUpdatedBy($this->currentUser->getId());
        $toEntry->setUpdatedBy($this->currentUser->getId());
      }

      $fromEntry->swapWith($toEntry);

      $fromEntry->save($transactionConnection);
      $toEntry->save($transactionConnection);

      $transactionConnection->commit();

      HttpUtils::prepareJsonResponse($response, [], '', 'success');
    } catch (\Exception $e) {
      $transactionConnection->rollBack();

      return $response
        ->withStatus(500)
        ->withHeader('x-custom-message', $e->getMessage())
        ->withHeader('x-custom-trace', $e->getTraceAsString());
    }

    return $response;
  }
}
