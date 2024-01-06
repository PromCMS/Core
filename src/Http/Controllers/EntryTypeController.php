<?php

namespace PromCMS\Core\Http\Controllers;

use Doctrine\Common\Collections\ArrayCollection;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Paginate;
use PromCMS\Core\Models\User;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Entity;
use PromCMS\Core\Session;
use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Utils\HttpUtils;
use Propel\Runtime\Map\TableMap;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Take care of normal models and singletons!
 */
class EntryTypeController
{
  protected User $currentUser;
  protected PromConfig $promConfig;
  protected EntityManager $em;

  public function __construct(Container $container)
  {
    $this->currentUser = $container->get(Session::class)->get('user', false);
    $this->promConfig = $container->get(PromConfig::class);
    $this->em = $container->get(EntityManager::class);
  }

  public function getInfo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $entity = $request->getAttribute(Entity::class);
    HttpUtils::prepareJsonResponse($response, $this->promConfig->getEntity($entity->tableName, true));

    return $response;
  }

  // TODO: Convert this to middleware
  private function getCurrentLanguage($request, $args)
  {
    $queryParams = $request->getQueryParams();
    $nextLanguage = $this->promConfig->getProject()->getDefaultLanguage();
    $supportedLanguages = $this->promConfig->getProject()->languages;

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

  // TODO: Sharable models should have join tables for user ids
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
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);

    // if ($this->isLocalizedModel($modelTableMap)) {
    //   $modelInstance->setLocale($this->getCurrentLanguage($request, $args));
    // }

    $parsedBody = $request->getParsedBody();

    try {
      if ($entity->sharable && $this->currentUser) {
        $parsedBody['data']['created_by'] = $this->currentUser->getId();
      }

      $instance = (new $entity->phpName);
      $instance->fill($parsedBody['data']);
      $this->em->persist($instance);
      $this->em->flush();

      HttpUtils::prepareJsonResponse($response, $instance->toArray());

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
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->getRepository($entity->phpName);

    // if ($this->isLocalizedModel($modelTableMap)) {
    //   $query->joinWithI18n($this->getCurrentLanguage($request, $args));
    // }

    if (!$entity->isSingleton()) {
      $item = $query->find(intval($args['itemId']));
      // $query->filterById($args['itemId']);

      // if ($request->getAttribute('permission-only-own') === true) {
      //   $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
      // }
    } else {
      $item = $query->findOneBy([]);
    }

    try {
      if (!$item) {
        throw new EntityNotFoundException();
      }

      HttpUtils::prepareJsonResponse(
        $response,
        $item->toArray()
      );

      return $response;
    } catch (\Exception | EntityNotFoundException $error) {
      // If it does not exist then create it
      if ($error instanceof EntityNotFoundException && $entity->isSingleton()) {
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
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->createQueryBuilder()->from($entity->phpName, 'i');

    if ($entity->isSingleton()) {
      return $response->withStatus(404);
    }

    // if ($this->isLocalizedModel($modelTableMap)) {
    //   $query->joinWithI18n($this->getCurrentLanguage($request, $args));
    // }

    $queryParams = $request->getQueryParams();
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $limit = intval($queryParams['limit'] ?? 15);

    // if ($request->getAttribute('permission-only-own', false) === true) {
    //   $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
    // }

    // TODO - make it more dynamic
    if (isset($queryParams['orderBy_created_at'])) {
      $query->orderBy("i.created_at", $queryParams['orderBy_created_at']);
    }
    $paginatedQuery = new Paginate($query);

    return ResponseHelper::withServerPagedResponse($response, $paginatedQuery->execute($page, $limit))->getResponse();
  }

  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->getRepository($entity->phpName);
    $parsedBody = $request->getParsedBody();

    // if ($this->isLocalizedModel($modelTableMap)) {
    //   $query->joinWithI18n($this->getCurrentLanguage($request, $args));
    // }

    if (!$entity->isSingleton()) {
      $item = $query->find(intval($args['itemId']));

      // if ($request->getAttribute('permission-only-own', false) === true) {
      //   $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
      // }
    } else {
      $item = $query->findOneBy([]);
    }

    if ($entity->sharable && $this->currentUser) {
      $parsedBody['data']['updated_by'] = $this->currentUser->getId();
    }

    try {
      if (!$item) {
        throw new EntityNotFoundException();
      }

      $item->fill($parsedBody['data']);
      $this->em->flush();

      HttpUtils::prepareJsonResponse($response, $item->toArray());

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
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->createQueryBuilder()->delete($entity->phpName, 'i');

    // if ($this->isLocalizedModel($modelTableMap)) {
    //   $query->joinWithI18n($this->getCurrentLanguage($request, $args));
    // }

    if (!$entity->isSingleton()) {
      $query->where("i.id", intval($args['itemId']));

      // if ($request->getAttribute('permission-only-own', false) === true) {
      //   $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
      // }
    }

    $result = $query->getQuery()->execute();

    if (empty($result)) {
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
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);

    if ($entity->isSingleton()) {
      return $response->withStatus(404);
    }

    $query = $this->em->getRepository($entity->tableName);
    $parsedBody = $request->getParsedBody();
    $data = $parsedBody['data'];

    if (
      !$entity->sorting ||
      empty($fromId = $data['fromId']) ||
      empty($toId = $data['toId']) ||
      $data['fromId'] === $data['toId']
    ) {
      return $response->withStatus(400);
    }

    $fromId = intval($fromId);
    $toId = intval($toId);

    // if ($request->getAttribute('permission-only-own', false) === true) {
    //   $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
    // }

    $items = new ArrayCollection($query->findBy([
      'id' => [$fromId, $toId]
    ]));

    if ($items->count() !== 2) {
      return $response->withStatus(400);
    }

    $this->em->getConnection()->beginTransaction();

    foreach ($items as $result) {
      if ($result->getId() === $fromId) {
        $fromEntry = $result;
      } else {
        $toEntry = $result;
      }
    }

    try {
      if ($entity->sharable && $this->currentUser) {
        $fromEntry->setUpdatedBy($this->currentUser->getId());
        $toEntry->setUpdatedBy($this->currentUser->getId());
      }

      $fromEntry->setOrder($toEntry);
      $toEntry->setOrder($fromEntry);

      $this->em->flush();
      $this->em->getConnection()->commit();

      HttpUtils::prepareJsonResponse($response, [], '', 'success');
    } catch (\Exception $e) {
      $this->em->getConnection()->rollBack();

      return $response
        ->withStatus(500)
        ->withHeader('x-custom-message', $e->getMessage())
        ->withHeader('x-custom-trace', $e->getTraceAsString());
    }

    return $response;
  }
}
