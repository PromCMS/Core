<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Internal\Http\Middleware\EntityPermissionMiddleware;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Internal\Http\Middleware\ModelMiddleware;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Entity;
use PromCMS\Core\Utils\HttpUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class EntitiesController
{
    #[AsApiRoute('GET', '/entry-types'), WithMiddleware(UserLoggedInMiddleware::class)]
    public function getInfoForEvery(ServerRequestInterface $request, ResponseInterface $response, PromConfig $promConfig): ResponseInterface
    {
        $collectedModelSummaries = [];

        foreach ($promConfig->getDatabaseModels() as $entity) {
            $collectedModelSummaries[$entity['tableName']] = $entity;
        }

        return ResponseHelper::withServerResponse($response, $collectedModelSummaries)->getResponse();
    }

    #[
        AsApiRoute('GET', '/entry-types/{modelId}'),
        WithMiddleware(UserLoggedInMiddleware::class),
        WithMiddleware(ModelMiddleware::class),
        WithMiddleware(EntityPermissionMiddleware::class),
    ]
    public function getInfo(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PromConfig $promConfig
    ): ResponseInterface {
        $entity = $request->getAttribute(Entity::class);
        HttpUtils::prepareJsonResponse($response, $promConfig->getEntityAsArray($entity->tableName));

        return $response;
    }
}
