<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Query\TranslationWalker;
use PromCMS\Core\Internal\Http\Middleware\EntityPermissionMiddleware;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Internal\Http\Middleware\SingletonMiddleware;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Entity;
use PromCMS\Core\Services\LocalizationService;
use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\Utils\HttpUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class SingletonController
{
  public function __construct(Container $container, private PromConfig $promConfig, protected EntityManager $em)
  {
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

  // Merge with getone and update => upsert
  public function create(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);

    // if ($this->isLocalizedModel($modelTableMap)) {
    //   $modelInstance->setLocale($this->getCurrentLanguage($request, $args));
    // }

    $parsedBody = $request->getParsedBody();
    $currentUser = $request->getAttribute('user');

    try {
      $instance = (new $entity->className);
      $instance->fill($parsedBody['data'] ?? []);

      if ($entity->sharable && $currentUser) {
        $instance->setCreatedBy($currentUser);
      }

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

  #[AsApiRoute('GET', '/singletons/{modelId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(SingletonMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function get(
    ServerRequestInterface $request,
    ResponseInterface $response,
    LocalizationService $localizationService
  ): ResponseInterface {
    $language = $request->getAttribute('lang');

    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->createQueryBuilder()
      ->from($entity->className, 'i')
      ->select('i')
      ->setMaxResults(1);

    $localize = $entity->localized && !$localizationService->isDefaultLanguage($language);
    $compiledQuery = $localize ? $this->getLocalizedQuery($query, $request) : $query->getQuery();
    $item = $compiledQuery->getOneOrNullResult();

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
        return $this->create($request, $response);
      }

      return $response
        ->withStatus(404)
        ->withHeader('Content-Description', $error->getMessage());
    }
  }

  private function getPayloadForUnlocalizedFields(Entity $entity, array $payload)
  {
    $columns = array_filter($entity->getColumns(), fn($column) => !$column->localized);
    $final = [];

    foreach ($columns as $column) {
      if (isset($payload[$column->name])) {
        $final[$column->name] = $payload[$column->name];
      }
    }

    return $final;
  }
  private function getPayloadForLocalizedFields(Entity $entity, array $payload)
  {
    $columns = array_filter($entity->getColumns(), fn($column) => $column->localized);
    $final = [];

    foreach ($columns as $column) {
      if (isset($payload[$column->name])) {
        $final[$column->name] = $payload[$column->name];
      }
    }

    return $final;
  }

  #[AsApiRoute('PATCH', '/singletons/{modelId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(SingletonMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    LocalizationService $localizationService
  ): ResponseInterface {
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->getRepository($entity->className);
    $parsedBody = $request->getParsedBody();
    $data = $parsedBody['data'];

    // if ($this->isLocalizedModel($modelTableMap)) {
    //   $query->joinWithI18n($this->getCurrentLanguage($request, $args));
    // }

    $item = $query->findOneBy([]);
    $currentUser = $request->getAttribute('user');

    try {
      if (!$item) {
        throw new EntityNotFoundException();
      }

      $language = $request->getAttribute('lang');
      $localize = $entity->localized && !$localizationService->isDefaultLanguage($language);

      if ($localize) {
        $unlocalizedDataPayload = $this->getPayloadForUnlocalizedFields($entity, $data);
        $localizedDataPayload = $this->getPayloadForLocalizedFields($entity, $data);

        $item->fill($unlocalizedDataPayload);
        $existingTranslations = $item->getTranslations();

        if (!isset($existingTranslations[$language])) {
          $translation = new($entity->getTranslationClassName());
          $translation->fill($item->toArray()); // Is it really necessary to fill it? Does every mutation need required fields to be copied?
        } else {
          $translation = $existingTranslations[$language];
        }

        $translation->fill($localizedDataPayload);
        $this->em->persist($translation);
      } else {
        $item->fill($data);
      }

      if ($entity->sharable && $currentUser) {
        $item->setUpdatedBy($currentUser);
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

  #[AsApiRoute('DELETE', '/singletons/{modelId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(SingletonMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    /** @var Entity */
    $entity = $request->getAttribute(Entity::class);
    $query = $this->em->createQueryBuilder()->delete($entity->className, 'i');

    // if ($this->isLocalizedModel($modelTableMap)) {
    //   $query->joinWithI18n($this->getCurrentLanguage($request, $args));
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
