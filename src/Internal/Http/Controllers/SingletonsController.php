<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use DI\Container;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\PromConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class SingletonsController
{
    private PromConfig $promConfig;

    public function __construct(Container $container)
    {
        $this->promConfig = $container->get(PromConfig::class);
    }

    public function getInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $collectedModelSummaries = [];

        foreach ($this->promConfig->getDatabaseSingletons() as $entity) {
            $collectedModelSummaries[$entity['tableName']] = $entity;
        }

        return ResponseHelper::withServerResponse($response, $collectedModelSummaries)->getResponse();
    }
}
