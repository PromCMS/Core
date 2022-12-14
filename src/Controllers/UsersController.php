<?php

namespace PromCMS\Core\Controllers;

use PromCMS\Core\Exceptions\EntityDuplicateException;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use DI\Container;
use PromCMS\Core\HttpUtils;
use PromCMS\Core\Models\Users;
use PromCMS\Core\Services\EntryTypeService;
use PromCMS\Core\Services\JWTService;
use PromCMS\Core\Services\PasswordService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class UsersController
{
  private $container;
  private $currentUser;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $this->passwordService = $container->get(PasswordService::class);
    $this->currentUser = $container->get('session')->get('user', false);
  }

  public function getInfo(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $instance = new Users();

    HttpUtils::prepareJsonResponse($response, (array) $instance->getSummary());

    return $response;
  }

  public function getMany(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $service = new EntryTypeService(new Users());
    $queryParams = $request->getQueryParams();
    $page = isset($queryParams['page']) ? $queryParams['page'] : 1;
    $limit = intval($queryParams['limit'] ?? 15);
    $where = [];

    if (isset($queryParams['where'])) {
      [$where] = HttpUtils::normalizeWhereQueryParam($queryParams['where']);
    }

    $response
      ->getBody()
      ->write(json_encode($service->getMany($where, $page, $limit)));

    return $response;
  }

  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    $parsedBody = $request->getParsedBody();
    $currentUser = $this->container->get('session')->get('user');

    if ($currentUser->id === $args['itemId']) {
      return $response
        ->withStatus(400)
        ->withHeader('Content-Description', 'cannot change self by this route');
    }

    if (isset($parsedBody['data']['password'])) {
      unset($parsedBody['data']['password']);
    }

    try {
      $user = Users::getOneById($args['itemId']);
      $updatedUser = $user->update($parsedBody['data']);

      HttpUtils::prepareJsonResponse($response, $updatedUser->getData());

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
      if (strval($this->currentUser->role) === '0') {
        $item = Users::getOneById($args['itemId']);
      } else {
        $item = Users::select(['id', 'name'])->getOneById($args['itemId']);
      }

      HttpUtils::prepareJsonResponse($response, $item->getData());

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
    $emailService = $this->container->get('email');
    $twigService = $this->container->get(Twig::class);

    if (isset($parsedBody['data']['password'])) {
      unset($parsedBody['data']['password']);
    }
    $parsedBody['data']['state'] = 'invited';

    // Generate random password, because user will choose their password by themselves
    $parsedBody['data']['password'] = $this->passwordService->generate(
      base64_encode(random_bytes(10)),
    );

    try {
      $user = Users::create($parsedBody['data']);
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

    $generatedJwt = $jwtService->generate(['id' => $user->id]);

    $themePayload = array_merge($user->getData(), [
      'token' => $generatedJwt,
      'app_url' => $_ENV['APP_URL'],
    ]);

    try {
      $generatedEmailContent = $twigService->render(
        'email/invite-user.twig',
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
    $emailService->addAddress($user->email, $user->name);
    $emailService->Subject = 'Finalize registration';
    $emailService->Body = $generatedEmailContent;
    $emailService->send();

    HttpUtils::prepareJsonResponse($response, $user->getData());

    return $response;
  }

  public function delete(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args
  ): ResponseInterface {
    HttpUtils::prepareJsonResponse(
      $response,
      Users::getOneById($args['itemId'])->delete(),
    );

    return $response;
  }

  public function block(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ) {
    $updatedUser = Users::updateById(intval($args['itemId']), [
      'state' => 'blocked',
    ]);

    HttpUtils::prepareJsonResponse($response, $updatedUser->getData());

    return $response;
  }

  public function unblock(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ) {
    $user = Users::where(['id', '=', intval($args['itemId'])])->getOne();

    if ($user->state !== 'blocked') {
      return $response->withStatus(400);
    }

    $updatedUser = $user->update([
      'state' => 'active',
    ]);

    HttpUtils::prepareJsonResponse($response, $updatedUser->getData());

    return $response;
  }

  public function requestPasswordReset(
    ServerRequestInterface $request,
    ResponseInterface $response,
    $args
  ) {
    $jwtService = $this->container->get(JWTService::class);
    $emailService = $this->container->get('email');
    $twigService = $this->container->get(Twig::class);

    $user = Users::getOneById($args['itemId']);
    if ($user->state === 'blocked') {
      return $response->withStatus(400);
    }

    $generatedJwt = $jwtService->generate(['id' => $user->id]);
    $themePayload = [
      'name' => $user->name,
      'email' => $user->email,
      'id' => $user->id,
      'token' => $generatedJwt,
      'app_url' => $_ENV['APP_URL'],
    ];

    try {
      $generatedEmailContent = $twigService->render(
        'email/password-reset',
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
    $emailService->addAddress($user->email, $user->name);
    $emailService->Subject = 'Password reset';
    $emailService->Body = $generatedEmailContent;

    // If user is invited the this whole function is for resending whole token
    if ($user->state !== 'invited') {
      $userData = $user
        ->update([
          'state' => 'password-reset',
        ])
        ->getData();
    }

    $emailService->send();

    HttpUtils::prepareJsonResponse($response, $userData);

    return $response;
  }
}
