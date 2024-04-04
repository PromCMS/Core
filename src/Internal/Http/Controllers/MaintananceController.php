<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Http\Middleware\InputValidationMiddleware;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\AsRouteGroup;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Schema;
use PromCMS\Core\Services\MaintananceService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
#[AsRouteGroup('/entry-types/prom__settings/maintanance')]
class MaintananceController
{
  #[AsApiRoute('POST', '/disabled')]
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
          "type" => "string",
        ],
        "description" => [
          "type" => "string",
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
    ])))
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
