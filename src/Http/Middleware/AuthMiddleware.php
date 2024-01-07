<?php

namespace PromCMS\Core\Http\Middleware;

use DI\Container;
use PromCMS\Core\Logger;
use PromCMS\Core\Services\UserService;
use PromCMS\Core\Session;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use GuzzleHttp\Psr7\Response;
use PromCMS\Core\Utils\HttpUtils;

class AuthMiddleware
{
  private Container $container;

  public function __construct(Container $container)
  {
    $this->container = $container;
  }

  /**
   * Auth middleware class, it interacts with session and gets if in session theres a user_id or throws 401
   *
   * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
   * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
   * @param  callable                                 $next     Next middleware
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function __invoke(Request $request, RequestHandler $handler)
  {
    $userId = $this->container->get(Session::class)->get('user_id', false);
    $userService = $this->container->get(UserService::class);

    if (!$userId) {
      $response = new Response();

      HttpUtils::prepareJsonResponse(
        $response,
        [],
        'User is not logged in',
        'not-logged-in',
      );

      return $response
        ->withStatus(401)
        ->withHeader('Content-Description', 'user logged off');
    } else {
      try {
        $this->container
          ->get(Session::class)
          ->set('user', $userService->getOneById(intval($userId)));
      } catch (\Exception $e) {
        $response = new Response();
        // User does not exist hence the session destroy
        $this->container->get(Session::class)::destroy();

        HttpUtils::prepareJsonResponse(
          $response,
          [],
          'User is not logged in',
          'not-logged-in',
        );

        $this->container->get(Logger::class)->error("Failed to get user in auth middleware, but session has user_id", [
          'error' => $e
        ]);

        return $response
          // TODO here should be different status code
          ->withStatus(500)
          ->withHeader('Content-Description', 'logged in user does not exist');
      }
    }

    return $handler->handle($request);
  }
}
