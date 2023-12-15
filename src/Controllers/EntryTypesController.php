<?php

namespace PromCMS\Core\Controllers;

use DI\Container;
use PromCMS\Core\Http\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EntryTypesController
{
    private $loadedModelNames;

    public function __construct(Container $container)
    {
        $this->loadedModelNames = $container->get('sysinfo')['loadedModels'];
    }

    public function getInfo(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $collectedModelSummaries = [];

        foreach ($this->loadedModelNames as $modelClassPath) {
            if ($modelClassPath::isSingleton()) {
                continue;
            }

            $metadata = $modelClassPath::getPromCmsMetadata();

            $collectedModelSummaries[$metadata['tableName']] = $metadata;
        }

        return ResponseHelper::withServerResponse($response, $collectedModelSummaries)->getResponse();
    }
}
