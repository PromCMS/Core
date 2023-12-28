<?php

namespace PromCMS\Core\Controllers;

use PromCMS\Core\Models\User;
use PromCMS\Core\Models\UserState;
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
use Propel\Runtime\Map\TableMap;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UsersController
{
  private $container;
  private UserService $userService;
  private $currentUser;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $this->userService = $container->get(UserService::class);
    $this->currentUser = $container->get(Session::class)->get('user', false);
  }

  public function getInfo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    HttpUtils::prepareJsonResponse($response, User::getPromCMSMetadata());

    return $response;
  }

  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $queryParams = $request->getQueryParams();
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $limit = intval($queryParams['limit'] ?? 15);
    $where = [];

    if (isset($queryParams['where'])) {
      [$where] = HttpUtils::normalizeWhereQueryParam($queryParams['where']);
    }

    return ResponseHelper::withServerPagedResponse($response, $this->userService->getManyPaged($page, $limit, $where))->getResponse();
  }

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
      $user->fromArray($parsedBody['data']);

      HttpUtils::prepareJsonResponse($response, $user->toArray(TableMap::TYPE_CAMELNAME));

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

  public function getOne(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    try {
      // If is not admin then we will return just id and name for safety reasons
      $currentUserIsAdmin = strval($this->currentUser->role) === '0';
      $item = $this->userService->getOneById($args['itemId'], $currentUserIsAdmin ? [] : ['Id', 'Name']);

      HttpUtils::prepareJsonResponse($response, $item->toArray(TableMap::TYPE_CAMELNAME));

      return $response;
    } catch (\Exception $e) {
      return $response->withStatus(404);
    }
  }

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
    $parsedBody['data']['state'] = UserState::$INVITED;

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

    $themePayload = array_merge($user->toArray(TableMap::TYPE_CAMELNAME), [
      'token' => $generatedJwt,
      'app_url' => $promConfig->getProjectUri()->__toString(),
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

    $userAsArray = $user->toArray(TableMap::TYPE_CAMELNAME);

    HttpUtils::prepareJsonResponse($response, $userAsArray);

    return $response;
  }

  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $this->userService->getOneById($args['itemId'])->delete();

    HttpUtils::prepareJsonResponse(
      $response, []
    );

    return $response;
  }

  public function block(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ) {
    $updatedUser = $this->userService->updateById(intval($args['itemId']), [
      'state' => UserState::$BLOCKED,
    ]);

    $userAsArray = $updatedUser->toArray(TableMap::TYPE_CAMELNAME);

    HttpUtils::prepareJsonResponse($response, $userAsArray);

    return $response;
  }

  public function unblock(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ) {
    $user = $this->userService->getOneById($args['itemId']);

    if (!$user->isBlocked()) {
      return $response->withStatus(400);
    }

    $updatedUser = $user->fromArray([
      'state' => UserState::$ACTIVE,
    ]);
    $updatedUser->save();

    HttpUtils::prepareJsonResponse($response, $updatedUser->getData());

    return $response;
  }

  public function requestPasswordReset(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ) {
    $jwtService = $this->container->get(JWTService::class);
    $emailService = $this->container->get(Mailer::class);
    $twigService = $this->container->get(RenderingService::class);
    $promConfig = $this->container->get(PromConfig::class);

    $user = $this->userService->getOneById($args['itemId']);

    if ($user->isBlocked()) {
      return $response->withStatus(400);
    }

    $generatedJwt = $jwtService->generate(['id' => $user->getId()]);
    $themePayload = [
      'name' => $user->getName(),
      'email' => $user->getEmail(),
      'id' => $user->getId(),
      'token' => $generatedJwt,
      'app_url' => $promConfig->getProjectUri()->__toString(),
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
        ->fromArray([
          'state' => UserState::$PASSWORD_RESET,
        ])
        ->save();
    }

    $emailService->send();

    HttpUtils::prepareJsonResponse($response, $user->toArray(TableMap::TYPE_CAMELNAME));

    return $response;
  }
}
