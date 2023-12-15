<?php

namespace PromCMS\Core\Controllers;

use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Models\UserRole;
use PromCMS\Core\Models\UserRoleQuery;
use PromCMS\Core\Services\UserRoleService;
use PromCMS\Core\Utils\HttpUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserRolesController
{
  private UserRoleService $userRoleService;
  public function __construct(Container $container)
  {
    $this->userRoleService = $container->get(UserRoleService::class);
  }

  public function getInfo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    HttpUtils::prepareJsonResponse($response, UserRole::getPromCMSMetadata());

    return $response;
  }

  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $queryParams = $request->getQueryParams();
    $page = intval(isset($queryParams['page']) ? $queryParams['page'] : 1);
    $limit = intval($queryParams['limit'] ?? 15);
    $responseData = UserRoleQuery::create()->paginate($page, $limit);

    if ($page === 1) {
      $responseData['data'] = array_merge(
        [
          $this->userRoleService::getAdminRole()
        ],
        $responseData['data'],
      );
    }

    return ResponseHelper::withServerResponse($response, $responseData)->getResponse();
  }

  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $parsedBody = $request->getParsedBody();

    try {
      $item = UserRoleQuery::create()->findOneById(intval($args['itemId']));

      if (!$item) {
        throw new EntityNotFoundException();
      }

      $item->fromArray($parsedBody['data']);
      $item->save();

      HttpUtils::prepareJsonResponse($response, $item->toArray());

      return $response;
    } catch (\Exception $ex) {
      if ($ex instanceof EntityDuplicateException) {
        HttpUtils::handleDuplicateEntriesError($response, $ex);

        return $response
          ->withStatus(400)
          ->withHeader('Content-Description', $ex->getMessage());
      } elseif ($ex instanceof EntityNotFoundException) {
        return $response
          ->withStatus(404)
          ->withHeader('Content-Description', $ex->getMessage());
      } else {
        return $response
          ->withStatus(500)
          ->withHeader('Content-Description', $ex->getMessage());
      }
    }
  }

  public function getOne(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $itemId = $args['itemId'];

    if ($itemId === '0') {
      HttpUtils::prepareJsonResponse($response, $this->userRoleService::getAdminRole());

      return $response;
    }

    $item = UserRoleQuery::create()->findOneById(intval($itemId));

    if (!$item) {
      throw new EntityNotFoundException();
    }

    try {
      HttpUtils::prepareJsonResponse(
        $response,
        $item->toArray(),
      );

      return $response;
    } catch (\Exception $e) {
      return $response->withStatus(404);
    }
  }

  public function create(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $parsedBody = $request->getParsedBody();

    try {
      $newItem = new UserRole();
      $newItem->fromArray($parsedBody['data']);
      $newItem->save();

      HttpUtils::prepareJsonResponse(
        $response,
        $newItem->toArray(),
      );

      return $response;
    } catch (\Exception $ex) {
      if ($ex instanceof EntityDuplicateException) {
        HttpUtils::handleDuplicateEntriesError($response, $ex);

        return $response
          ->withStatus(400)
          ->withHeader('Content-Description', $ex->getMessage());
      } else {
        return $response
          ->withStatus(500)
          ->withHeader('Content-Description', $ex->getMessage());
      }
    }
  }

  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    UserRoleQuery::create()->filterById(intval($args['itemId']))->delete();

    HttpUtils::prepareJsonResponse(
      $response,
      [],
    );

    return $response;
  }
}
