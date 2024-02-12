<?php

namespace PromCMS\Core\Http\Middleware;

use PromCMS\Core\Logger;
use PromCMS\Core\Services\UserService;
use PromCMS\Core\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use GuzzleHttp\Psr7\Response;
use PromCMS\Core\Utils\HttpUtils;

class UserLoggedInMiddleware implements MiddlewareInterface
{

  public function __construct(private Session $session, private UserService $userService, private Logger $logger)
  {
  }

  /**
   * Auth middleware class, it interacts with session and gets if in session theres a user_id or throws 401
   */
  public function process(Request $request, RequestHandler $handler): ResponseInterface
  {
    $userId = $this->session->get('user_id', false);

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
        $request = $request->withAttribute('user', $this->userService->getOneById(intval($userId)));
      } catch (\Exception $e) {
        $response = new Response();
        // User does not exist hence the session destroy
        $this->session::destroy();

        HttpUtils::prepareJsonResponse(
          $response,
          [],
          'User is not logged in',
          'not-logged-in',
        );

        $this->logger->error("Failed to get user in auth middleware, but session has user_id", [
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
