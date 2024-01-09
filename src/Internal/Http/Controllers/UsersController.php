<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Internal\Http\Middleware\EntityPermissionMiddleware;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\AsRouteGroup;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Http\WhereQueryParam;
use PromCMS\Core\Database\Models\Base\UserState;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Internal\Http\Middleware\EntityMiddleware;
use PromCMS\Core\Password;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Services\UserService;
use PromCMS\Core\Session;
use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\Http\ResponseHelper;
use PromCMS\Core\Services\RenderingService;
use PromCMS\Core\Utils\HttpUtils;
use PromCMS\Core\Mailer;
use PromCMS\Core\Services\JWTService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
#[AsRouteGroup('/entry-types/{modelId:users|prom__users}')]
class UsersController
{
  private $container;
  private UserService $userService;
  private PromConfig $promConfig;
  private User $currentUser;
  private EntityManager $em;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $this->userService = $container->get(UserService::class);
    $this->promConfig = $container->get(PromConfig::class);
    $this->currentUser = $container->get(Session::class)->get('user', false);
    $this->em = $container->get(EntityManager::class);
  }

  #[AsApiRoute('GET', '/items'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(EntityMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $queryParams = $request->getQueryParams();
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $limit = intval($queryParams['limit'] ?? 15);
    $where = null;

    if (isset($queryParams['where'])) {
      $where = new WhereQueryParam($queryParams['where']);
    }

    return ResponseHelper::withServerPagedResponse($response, $this->userService->getManyPaged($page, $limit, $where))->getResponse();
  }

  #[AsApiRoute('PATCH', '/items/{itemId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(EntityMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $parsedBody = $request->getParsedBody();
    $currentUser = $this->container->get(Session::class)->get('user');

    if ($currentUser->getId() === intval($args['itemId'])) {
      return $response
        ->withStatus(400)
        ->withHeader('Content-Description', 'cannot change self by this route');
    }

    if (isset($parsedBody['data']['password'])) {
      unset($parsedBody['data']['password']);
    }

    try {
      $user = $this->userService->getOneById($args['itemId']);
      $user->fill($parsedBody['data']);

      HttpUtils::prepareJsonResponse($response, $user->toArray());

      return $response;
    } catch (\Exception $ex) {
      $response = $response->withHeader(
        'Content-Description',
        $ex->getMessage(),
      );

      if ($ex instanceof EntityDuplicateException) {
        HttpUtils::handleDuplicateEntriesError($response, $ex);
        return $response->withStatus(400);
      } elseif ($ex instanceof EntityNotFoundException) {
        return $response->withStatus(404);
      } else {
        return $response->withStatus(500);
      }
    }
  }

  // Here you would expect EntityPermissionMiddleware, but not quite! Other users can view others email, id and name
  // Thats because user email. id and name is used to display contributors on entity
  #[AsApiRoute('GET', '/items/{itemId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(EntityMiddleware::class),
  ]
  public function getOne(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    try {
      $item = $this->userService->getOneById(
        $args['itemId'],
        $this->currentUser->isAdmin() ? [] : ['id', 'name']
      );

      HttpUtils::prepareJsonResponse($response, $item->toArray());

      return $response;
    } catch (\Exception $e) {
      return $response->withStatus(404);
    }
  }

  #[AsApiRoute('POST', '/items/create'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(EntityMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function create(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $parsedBody = $request->getParsedBody();
    $jwtService = $this->container->get(JWTService::class);
    $emailService = $this->container->get(Mailer::class);
    $twigService = $this->container->get(RenderingService::class);
    $promConfig = $this->container->get(PromConfig::class);

    if (isset($parsedBody['data']['password'])) {
      unset($parsedBody['data']['password']);
    }
    $parsedBody['data']['state'] = UserState::INVITED;

    // Generate random password, because user will choose their password by themselves
    $parsedBody['data']['password'] = Password::hash(
      base64_encode(random_bytes(10)),
    );

    try {
      $user = $this->userService->create($parsedBody['data']);
    } catch (\Exception $ex) {
      $response = $response->withHeader(
        'Content-Description',
        $ex->getMessage(),
      );

      if ($ex instanceof EntityDuplicateException) {
        HttpUtils::handleDuplicateEntriesError($response, $ex);
        return $response->withStatus(400);
      } elseif ($ex instanceof EntityNotFoundException) {
        return $response->withStatus(404);
      } else {
        return $response->withStatus(500);
      }
    }

    $generatedJwt = $jwtService->generate(['id' => $user->getId()]);

    $themePayload = array_merge($user->toArray(), [
      'token' => $generatedJwt,
      'app_url' => $promConfig->getProject()->url->__toString(),
    ]);

    try {
      $generatedEmailContent = $twigService->getEnvironment()->render(
        $emailService->getInviteUserTemplatePath(),
        $themePayload,
      );
    } catch (\Exception $e) {
      $loader = new \Twig\Loader\ArrayLoader([
        'index' =>
          'Welcome, {{ name }}! Please continue with your registration <a href="{{ app_url }}/admin/finalize-registration?token={{ token }}">here</a>!',
      ]);
      $twig = new \Twig\Environment($loader);

      $generatedEmailContent = $twig->render('index', $themePayload);
    }

    $emailService->isHtml();
    $emailService->addAddress($user->email, $user->getName());
    $emailService->Subject = 'Finalize registration';
    $emailService->Body = $generatedEmailContent;
    $emailService->send();

    $userAsArray = $user->toArray();

    HttpUtils::prepareJsonResponse($response, $userAsArray);

    return $response;
  }

  #[AsApiRoute('DELETE', '/items/{itemId}'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(EntityMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $user = $this->userService->getOneById(intval($args['itemId']));

    if ($this->currentUser->getId() === $user->getId()) {
      return $response->withStatus(404);
    }

    $this->em->remove($user);
    $this->em->flush();

    HttpUtils::prepareJsonResponse(
      $response,
      []
    );

    return $response;
  }

  #[AsApiRoute('PATCH', '/items/{itemId}/block'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(EntityMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function block(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ) {
    $updatedUser = $this->userService->updateById(intval($args['itemId']), [
      'state' => UserState::BLOCKED,
    ]);

    $userAsArray = $updatedUser->toArray();

    HttpUtils::prepareJsonResponse($response, $userAsArray);

    return $response;
  }

  #[AsApiRoute('PATCH', '/items/{itemId}/unblock'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(EntityMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function unblock(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ) {
    $user = $this->userService->getOneById($args['itemId']);

    if (!$user->isBlocked()) {
      return $response->withStatus(400);
    }

    $user->fill([
      'state' => UserState::ACTIVE,
    ]);

    $this->em->flush();

    HttpUtils::prepareJsonResponse($response, $user->toArray());

    return $response;
  }

  #[AsApiRoute('PATCH', '/items/{itemId}/request-password-reset'),
    WithMiddleware(UserLoggedInMiddleware::class),
    WithMiddleware(EntityMiddleware::class),
    WithMiddleware(EntityPermissionMiddleware::class),
  ]
  public function requestPasswordReset(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ) {
    $jwtService = $this->container->get(JWTService::class);
    $emailService = $this->container->get(Mailer::class);
    $twigService = $this->container->get(RenderingService::class);
    $promConfig = $this->container->get(PromConfig::class);

    $user = $this->userService->getOneById(intval($args['itemId']));

    if ($user->isBlocked()) {
      return $response->withStatus(400);
    }

    $generatedJwt = $jwtService->generate(['id' => $user->getId()]);
    $themePayload = [
      'name' => $user->getName(),
      'email' => $user->getEmail(),
      'id' => $user->getId(),
      'token' => $generatedJwt,
      'app_url' => $promConfig->getProject()->url->__toString(),
    ];

    try {
      $generatedEmailContent = $twigService->getEnvironment()->render(
        $emailService->getPasswordResetTemplatePath(),
        $themePayload,
      );
    } catch (\Exception $e) {
      $loader = new \Twig\Loader\ArrayLoader([
        'index' =>
          'Hey, {{ name }}! We noticed that you requested a password reset. Please continue <a href="{{ app_url }}/admin/reset-password?token={{ token }}">here</a>!',
      ]);
      $twig = new \Twig\Environment($loader);

      $generatedEmailContent = $twig->render('index', $themePayload);
    }

    $emailService->isHtml();
    $emailService->addAddress($user->email, $user->getName());
    $emailService->Subject = 'Password reset';
    $emailService->Body = $generatedEmailContent;

    // If user is invited the this whole function is for resending whole token
    if ($user->state !== 'invited') {
      $user
        ->fill([
          'state' => UserState::PASSWORD_RESET,
        ]);

      $this->em->flush();
    }

    $emailService->send();

    HttpUtils::prepareJsonResponse($response, $user->toArray());

    return $response;
  }
}
