<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Http\Middleware\InputValidationMiddleware;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\AsRouteGroup;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Internal\Http\Middleware\EntityPermissionMiddleware;
use PromCMS\Core\Internal\Http\Middleware\ModelMiddleware;
use PromCMS\Core\Schema;
use PromCMS\Core\Services\MaintananceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
#[AsRouteGroup('/entry-types/{modelId:prom__settings}/maintanance')]
class MaintananceController
{
  #[
    AsApiRoute('GET', '/'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function getInfoAsBatch(
    ServerRequestInterface $request,
    ResponseInterface $response,
    MaintananceService $maintananceService
  ): ResponseInterface {
    return ResponseHelper::withServerResponse($response, [
      'enabled' => $maintananceService->isEnabled(),
      'title' => $maintananceService->getTitle(),
      'description' => $maintananceService->getDescription(),
      'countdown' => $maintananceService->getCountdownTimestamp()
    ])->getResponse();
  }

  #[
    AsApiRoute('POST', '/disable'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function disable(
    ServerRequestInterface $request,
    ResponseInterface $response,
    MaintananceService $maintananceService
  ): ResponseInterface {
    $maintananceService->disable();

    return $response;
  }

  #[
    AsApiRoute('POST', '/enable'),
    WithMiddleware(new InputValidationMiddleware(new Schema([
      "type" => "object",
      "properties" => [
        "title" => [
          "anyOf" => [
            [
              "type" => "string"
            ],
            [
              "type" => "null"
            ]
          ]
        ],
        "description" => [
          "anyOf" => [
            [
              "type" => "string"
            ],
            [
              "type" => "null"
            ]
          ]
        ],
        "countdown" => [
          "anyOf" => [
            [
              "type" => "string",
              "format" => "date-time"
            ],
            [
              "type" => "null"
            ]
          ]
        ]
      ]
    ]))),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(ModelMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function enable(
    ServerRequestInterface $request,
    ResponseInterface $response,
    MaintananceService $maintananceService
  ): ResponseInterface {
    $parsedBody = $request->getParsedBody();
    $maintananceService->enable($parsedBody);

    return $response;
  }
}
