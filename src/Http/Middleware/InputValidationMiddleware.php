<?php

namespace PromCMS\Core\Http\Middleware;

use PromCMS\Core\Schema;
use PromCMS\Core\Utils\ObjectUtils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class InputValidationMiddleware implements MiddlewareInterface
{
  private array $onlyMethods = ['post', 'patch', 'get'];

  public function __construct(
    private readonly Schema $schema,
    private readonly int $constraint = \JsonSchema\Constraints\Constraint::CHECK_MODE_APPLY_DEFAULTS
  ) {
  }

  /**
   * Auth middleware class, it interacts with session and gets if in session theres a user_id or throws 401
   */
  public function process(Request $request, RequestHandler $handler): ResponseInterface
  {
    $requestMethod = strtolower($request->getMethod());

    if (in_array($requestMethod, $this->onlyMethods)) {
      try {
        $data = match ($requestMethod) {
          'post', 'patch' => $request->getParsedBody(),
          'get' => $request->getQueryParams(),
          default => []
        } ?? [];

        $validatedBody = $this->schema->validate($data, $this->constraint);

        $request = $request->withAttribute('validatedBody', ObjectUtils::objectToArrayRecursive($validatedBody));
      } catch (\Exception $error) {
        // TODO: better indications on what fields failed

        return (new Response())
          ->withStatus(400)
          ->withHeader('Content-Description', $error->getMessage());
      }
    }

    return $handler->handle($request);
  }
}