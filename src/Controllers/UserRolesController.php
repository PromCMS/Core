<?php

namespace PromCMS\Core\Controllers;

use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\Utils\HttpUtils;;
use PromCMS\Core\Models\UserRoles;
use PromCMS\Core\Services\EntryTypeService;
use PromCMS\Core\Services\PasswordService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserRolesController
{
  private PasswordService $passwordService;
  public function __construct(Container $container)
  {
    $this->passwordService = $container->get(PasswordService::class);
  }

  public function getInfo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $instance = new UserRoles();

    HttpUtils::prepareJsonResponse($response, (array) $instance->getSummary());

    return $response;
  }

  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $queryParams = $request->getQueryParams();
    $page = intval(isset($queryParams['page']) ? $queryParams['page'] : 1);
    $service = new EntryTypeService(new UserRoles());
    $limit = intval($queryParams['limit'] ?? 15);
    $responseData = $service->getMany([], $page, $limit);

    if ($page === 1) {
      $responseData['data'] = array_merge(
        [
          [
            'id' => 0,
            'label' => 'Admin',
            'slug' => 'admin',
            'description' => 'Main user role provided by PromCMS Core module',
          ],
        ],
        $responseData['data'],
      );
    }

    $response->getBody()->write(json_encode($responseData));

    return $response;
  }

  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $parsedBody = $request->getParsedBody();
    $classInstance = new UserRoles();

    try {
      $item = $classInstance->getOneById($args['itemId']);
      $item->update($parsedBody['data']);

      HttpUtils::prepareJsonResponse($response, $item->getData());

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
    $classInstance = new UserRoles();

    // For admin we return few static values
    if ($itemId === '0') {
      HttpUtils::prepareJsonResponse($response, [
        'id' => 0,
        'label' => 'Admin',
        'slug' => 'admin',
      ]);

      return $response;
    }

    try {
      HttpUtils::prepareJsonResponse(
        $response,
        $classInstance
          ->where(['id', '=', intval($itemId)])
          ->getOne()
          ->getData(),
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
    $classInstance = new UserRoles();

    try {
      HttpUtils::prepareJsonResponse(
        $response,
        $classInstance->create($parsedBody['data'])->getData(),
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
    $classInstance = new UserRoles();

    HttpUtils::prepareJsonResponse(
      $response,
      $classInstance
        ->where(['id', '=', intval($args['itemId'])])
        ->delete()
        ->getData(),
    );

    return $response;
  }
}
