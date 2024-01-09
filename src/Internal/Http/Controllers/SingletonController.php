<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Internal\Http\Middleware\EntityPermissionMiddleware;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Internal\Http\Middleware\SingletonMiddleware;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Entity;
use PromCMS\Core\Session;
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
  protected User $currentUser;

  public function __construct(Container $container, private PromConfig $promConfig, protected EntityManager $em)
  {
    $this->currentUser = $container->get(Session::class)->get('user', false);
  }

  // Merge with getone and update => upsert
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

  #[AsApiRoute('GET', '/singletons/{modelId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(SingletonMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function get(
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

    $item = $query->findOneBy([]);

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

  #[AsApiRoute('PATCH', '/singletons/{modelId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(SingletonMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
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

    $item = $query->findOneBy([]);

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

  #[AsApiRoute('DELETE', '/singletons/{modelId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(SingletonMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
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