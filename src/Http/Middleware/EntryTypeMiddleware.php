<?php

namespace PromCMS\Core\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

class EntryTypeMiddleware
{
    private $loadedModels;
    private bool $singletonsOnly;
    private array $modelSlugToModelReference = [];

    public function __construct($container, $singletonsOnly = true)
    {
        $this->loadedModels = $container->get('sysinfo')['loadedModels'];
        $this->singletonsOnly = $singletonsOnly;

        foreach ($this->loadedModels as $loadedModelClassReference) {
            $tableMap = ($loadedModelClassReference)::TABLE_MAP;


            $this->modelSlugToModelReference[$tableMap::TABLE_NAME] = $loadedModelClassReference;
        }
    }

    /**
     * Permission middleware class, it interacts with session and gets if in session theres a sufficient user role for provided route
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $modelId = $route->getArgument('modelId');
        $SINGLETON_PREFIX = "singleton_";

        if ($this->singletonsOnly && !str_starts_with($modelId, $SINGLETON_PREFIX)) {
            $modelId = $SINGLETON_PREFIX . $modelId;
        }

        if (empty($modelInstancePath = $this->modelSlugToModelReference[$modelId])) {
            // TODO: this should be dropped
            if ($modelId === "user-roles" || strtolower($modelId) === 'userroles') {
                $modelId = 'user_roles';
            } else if ($modelId === "generalTranslations") {
                $modelId = 'general_translations';
            }

            // TODO: remove this after you have ensured that everything works correctly as we probably dont want this check to be here and take models from url as is
            $modelId = "prom__$modelId";

            if (empty($modelInstancePath = $this->modelSlugToModelReference[$modelId])) {
                $response = new Response();

                return $response
                    ->withStatus(404)
                    ->withHeader('Content-Description', 'Model does not exist');
            }
        }

        // Attach on request to pass the model instance info
        $request = $request->withAttribute('model', (object) [
            "entry" => $modelInstancePath,
            "map" => ($modelInstancePath)::TABLE_MAP,
            "query" => $modelInstancePath . "Query"
        ]);

        return $handler->handle($request);
    }
}
