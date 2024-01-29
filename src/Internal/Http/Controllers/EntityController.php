<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Paginate;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Database\Query\TranslationWalker;
use PromCMS\Core\Internal\Http\Middleware\EntityPermissionMiddleware;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Internal\Http\Middleware\ModelMiddleware;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Entity;
use PromCMS\Core\Services\LocalizationService;
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
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class EntityController
{
  protected ?User $currentUser;

  public function __construct(Container $container, private PromConfig $promConfig, protected EntityManager $em)
  {
    $this->currentUser = $container->get(Session::class)->get('user', null);
  }

  // TODO: Sharable models should have join tables for user ids
  private function filterQueryOnlyToOwners(TableMap $modelTableMap, User $currentUser, &$query)
  {
    $query->filterBy("createdBy", $currentUser->getId());

    if ($this->isSharableModel($modelTableMap)) {
      $query
        ->_or()
        ->filterBy("coeditors.user_id", $currentUser->getId());
    }
  }

  private function getLocalizedQuery(QueryBuilder $query, ServerRequestInterface $request)
  {
    $compiledQuery = $query->getQuery();

    $compiledQuery
      ->setHint(
        Query::HINT_CUSTOM_OUTPUT_WALKER,
        TranslationWalker::class
      )
      ->setHint(TranslationWalker::HINT_LOCALE, $request->getAttribute('lang'));

    return $compiledQuery;
  }

  #[AsApiRoute('POST', '/entry-types/{modelId}/items/create'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function create(
    ServerRequestInterface $request,
    ResponseInterface $response,
  ): ResponseInterface {
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);

    // There is no need to set language on create as create should take default language

    $parsedBody = $request->getParsedBody();

    try {
      if ($entity->sharable && $this->currentUser) {
        $parsedBody['data']['createdBy'] = $this->currentUser;
      }

      $instance = (new $entity->className);
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

  #[AsApiRoute('GET', '/entry-types/{modelId}/items/{itemId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function getOne(
    ServerRequestInterface $request,
    ResponseInterface $response,
    LocalizationService $localizationService
  ): ResponseInterface {
    $itemId = $request->getAttribute('itemId');
    $language = $request->getAttribute('lang');

    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->createQueryBuilder()
      ->from($entity->className, 'i')
      ->select('i')
      ->setMaxResults(1)
      ->where('i.id = :id')
      ->setParameter(':id', intval($itemId));

    $localize = $entity->localized && !$localizationService->isDefaultLanguage($language);
    $compiledQuery = $localize ? $this->getLocalizedQuery($query, $request) : $query->getQuery();
    $item = $compiledQuery->getOneOrNullResult();

    // if ($request->getAttribute('permission-only-own') === true) {
    //   $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
    // }

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

      return $response
        ->withStatus(404)
        ->withHeader('Content-Description', $error->getMessage());
    }
  }

  #[AsApiRoute('GET', '/entry-types/{modelId}/items'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response,
  ): ResponseInterface {
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->createQueryBuilder()->from($entity->className, 'i')->select('i');

    $queryParams = $request->getQueryParams();
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $limit = intval($queryParams['limit'] ?? 15);

    // if ($request->getAttribute('permission-only-own', false) === true) {
    //   $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
    // }

    // TODO - make it more dynamic
    if (isset($queryParams['orderBy_created_at'])) {
      $query->orderBy("i.createdAt", $queryParams['orderBy_created_at']);
    }

    return ResponseHelper::withServerPagedResponse($response, Paginate::fromQuery($this->getLocalizedQuery($query, $request))->execute($page, $limit))->getResponse();
  }

  #[AsApiRoute('PATCH', '/entry-types/{modelId}/items/reorder'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function swapTwo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);

    $query = $this->em->getRepository($entity->className);
    $data = $request->getParsedBody();

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
        $fromEntry->setUpdatedBy($this->currentUser);
        $toEntry->setUpdatedBy($this->currentUser);
      }

      $fromEntry->setOrder($toEntry->getOrder() ?? $toEntry->getId());
      $toEntry->setOrder($fromEntry->getOrder() ?? $fromEntry->getId());

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

  #[AsApiRoute('PATCH', '/entry-types/{modelId}/items/{itemId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    LocalizationService $localizationService
  ): ResponseInterface {
    $itemId = $request->getAttribute('itemId');

    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->getRepository($entity->className);
    $parsedBody = $request->getParsedBody();
    $item = $query->find(intval($itemId));

    // if ($request->getAttribute('permission-only-own', false) === true) {
    //   $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
    // }

    if ($entity->sharable && $this->currentUser) {
      $parsedBody['data']['updatedBy'] = $this->currentUser;
    }

    try {
      if (!$item) {
        throw new EntityNotFoundException();
      }

      $language = $request->getAttribute('lang');
      $localize = $entity->localized && !$localizationService->isDefaultLanguage($language);

      if ($localize) {
        $item->fill($parsedBody['data'], $language);

        foreach ($item->getTranslations() as $translation) {
          $this->em->persist($translation);
        }
      } else {
        $item->fill($parsedBody['data']);
      }

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

  #[AsApiRoute('DELETE', '/entry-types/{modelId}/items/{itemId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response,
  ): ResponseInterface {
    $itemId = $request->getAttribute('itemId');

    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->createQueryBuilder()
      ->delete($entity->className, 'i')
      ->setMaxResults(1)
      ->where("i.id", ':id')->setParameter(':id', intval($itemId));

    // if ($request->getAttribute('permission-only-own', false) === true) {
    //   $this->filterQueryOnlyToOwners($modelTableMap, $this->currentUser, $query);
    // }

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
}
