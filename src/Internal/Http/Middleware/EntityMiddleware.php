<?php

namespace PromCMS\Core\Internal\Http\Middleware;

use DI\Container;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Entity;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

abstract class EntityMiddleware implements MiddlewareInterface
{
    private PromConfig $promConfig;

    private array $tableNames;

    public function __construct(Container $container, EntityMiddlewareMode $mode)
    {
        $this->promConfig = $container->get(PromConfig::class);

        $entities = [];
        if ($mode === EntityMiddlewareMode::MODEL) {
            $entities = $this->promConfig->getDatabaseModels();
        } else if ($mode === EntityMiddlewareMode::SINGLETON) {
            $entities = $this->promConfig->getDatabaseSingletons();
        }

        $this->tableNames = array_map(fn($entity) => $entity['tableName'], $entities);
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
    public function process(Request $request, RequestHandler $handler): Response
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $modelTableName = $route->getArgument('modelId');

        if (!in_array($modelTableName, $this->tableNames)) {
            // TODO: this should be dropped
            // TODO: remove this after you have ensured that everything works correctly as we probably dont want this check to be here and take models from url as is
            $modelTableName = 'prom__' . match ($modelTableName) {
                'generalTranslations' => 'general_translations',
                default => $modelTableName
            };

            if (!in_array($modelTableName, $this->tableNames)) {
                $response = new Response();

                return $response
                    ->withStatus(404)
                    ->withHeader('Content-Description', 'Model does not exist');
            }
        }

        // Attach on request to pass the model instance info
        $request = $request->withAttribute(Entity::class, $this->promConfig->getEntity($modelTableName));

        return $handler->handle($request);
    }
}
