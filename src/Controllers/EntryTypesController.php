<?php

namespace PromCMS\Core\Controllers;

use DI\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EntryTypesController
{
    private $loadedModelNames;

    public function __construct(Container $container)
    {
        $this->loadedModelNames = $container->get('sysinfo')["loadedModels"];
    }

    public function getInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $collectedModelSummaries = [];

        foreach ($this->loadedModelNames as $modelClassName) {
            $modelClass = new $modelClassName();
            $modelName = end(explode('\\', $modelClassName));
            $collectedModelSummaries[$modelName] = $modelClass->getSummary();
        }

        $response->getBody()->write(json_encode(
            $collectedModelSummaries
        ));

        return $response;
    }
}
