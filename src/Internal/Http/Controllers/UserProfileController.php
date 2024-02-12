<?php

namespace PromCMS\Core\Internal\Http\Controllers;

use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Models\Base\UserState;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Http\Middleware\UserLoggedInMiddleware;
use PromCMS\Core\Http\Routing\AsApiRoute;
use PromCMS\Core\Http\Routing\WithMiddleware;
use PromCMS\Core\Logger;
use PromCMS\Core\Password;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Services\FileService;
use PromCMS\Core\Services\UserService;
use PromCMS\Core\Session;
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
class UserProfileController
{
  public function __construct(
    private JWTService $jwt,
    private Session $session,
    private UserService $userService,
    private EntityManager $em,
    private Logger $logger
  ) {
  }

  private function getQb()
  {
    return $this->em->createQueryBuilder();
  }

  #[AsApiRoute('GET', '/profile/me'),
    WithMiddleware(UserLoggedInMiddleware::class)]
  public function getCurrent(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $user = $request->getAttribute('user');

    return ResponseHelper::withServerResponse($response, $user->toArray())->getResponse();
  }

  #[AsApiRoute('POST', '/profile/login')]
  public function login(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $userId = $this->session->get('user_id', false);
    $args = $request->getParsedBody();
    $code = 200;
    $responseAry = [
      'result' => 'success',
    ];

    if ($userId !== false) {
      $responseAry['result'] = 'success';
      $responseAry['message'] = 'already logged in';
      $code = 200;
    }

    if (!isset($args['password']) || !isset($args['email'])) {
      $responseAry['result'] = 'error';
      $responseAry['message'] = 'missing params';
      $code = 400;
    } else {
      $userCannotLoginBecauseOfState = false;

      try {
        $user = $this->userService->getOneBy("email", $args['email']);
        $userState = $user->getState();

        if (!$user->checkPassword($args['password'])) {
          throw new \Exception('Wrong password');
        }

        if (
          $userState === 'password-reset' ||
          $userState === 'blocked' ||
          $userState === 'invited'
        ) {
          $userCannotLoginBecauseOfState = true;
          throw new \Exception("user-state-$userState");
        }

        $this->session->set('user_id', $user->getId());
        $responseAry['data'] = $user->toArray();
        $responseAry['result'] = 'success';
        $responseAry['message'] = 'successfully logged in';
        $code = 200;
      } catch (\Exception $e) {
        if (str_contains($e->getMessage(), 'does not have any password')) {
          $this->logger->error('User does not have any password and we prevented their log in, please check this in database', [
            'error' => $e,
            'user' => [
              'id' => $user->getId()
            ]
          ]);
        }

        if ($userCannotLoginBecauseOfState) {
          $responseAry['result'] = 'error';
          $responseAry['message'] = 'user cannot login';
          $responseAry['code'] = $e->getMessage();
        } else {
          $responseAry['result'] = 'error';
          $responseAry['code'] = 'invalid-credentials';
          $responseAry['message'] = 'wrong password or email';
        }

        $code = 400;
      }
    }

    return ResponseHelper::withServerResponse($response, $responseAry, $code)->getResponse();
  }

  #[AsApiRoute('POST', '/profile/update'),
    WithMiddleware(UserLoggedInMiddleware::class)]
  public function update(
    ServerRequestInterface $request,
    ResponseInterface $response,
    FileService $fileService
  ): ResponseInterface {
    /** @var User */
    $user = $request->getAttribute('user');
    $parsedBody = $request->getParsedBody();

    if (!$parsedBody['data']) {
      return $response->withStatus(400);
    }
    $data = $parsedBody['data'];

    if (isset($data['id'])) {
      unset($data['id']);
    }

    // Unset items that user should not remove by themselves
    if (isset($data['password'])) {
      unset($data['password']);
    }

    if (isset($data['role'])) {
      unset($data['role']);
    }

    if (isset($data['state'])) {
      unset($data['state']);
    }

    if (isset($data['avatar']) && $data['avatar'] !== null) {
      $data['avatar'] = $fileService->getById($data['avatar']);
    }

    $user->fill($data);
    $this->em->flush();

    return ResponseHelper::withServerResponse($response, $user->toArray())->getResponse();
  }

  #[AsApiRoute('GET', '/profile/logout'),
    WithMiddleware(UserLoggedInMiddleware::class)]
  public function logout(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $this->session::destroy();

    return $response;
  }

  /**
   * User have forgot theirs password and requested renewal when logged of
   */
  #[AsApiRoute('GET', '/profile/request-password-reset')]
  public function requestPasswordReset(
    ServerRequestInterface $request,
    ResponseInterface $response,
    Mailer $emailService,
    RenderingService $twigService,
    PromConfig $promConfig
  ) {
    $params = $request->getQueryParams();
    $expr = $this->getQb()->expr();

    if (!$params['email']) {
      return $response->withStatus(400);
    }

    try {
      $user = $this->userService->findOneBy(
        $expr->andX(
          $expr->eq('u.email', $params['email']),
          $expr->not($expr->eq('u.state', UserState::BLOCKED))
        )
      );
    } catch (\Exception $e) {
      // We did not find user on provided email, but we do not want to let user know about it since we do not want to expose anything to public
      return $response;
    }

    $generatedJwt = $this->jwt->generate(['id' => $user->id]);
    $themePayload = [
      'name' => $user->getName(),
      'email' => $user->email,
      'id' => $user->id,
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

    $this->userService->updateById($user->getId(), [
      'state' => UserState::PASSWORD_RESET,
    ]);

    $emailService->send();

    return $response;
  }

  /**
   * Finalizing of password renewal via token
   */
  #[AsApiRoute('POST', '/profile/change-password'),
    WithMiddleware(UserLoggedInMiddleware::class)]
  public function changePassword(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $params = $request->getParsedBody();
    $user = $request->getAttribute('user');

    // Check that we atleast have something
    if (!isset($params['newPassword']) || !isset($params['oldPassword'])) {
      HttpUtils::prepareJsonResponse($response, [], "Missing body values", 'missing-body-values');
      return $response->withStatus(401);
    }

    // Get values
    $newPassword = $params['newPassword'];
    $oldPassword = $params['oldPassword'];

    // Validate old password
    if (!Password::check($oldPassword, $user->password)) {
      HttpUtils::prepareJsonResponse($response, [], "Old password invalid", 'old-password-invalid');
      return $response->withStatus(401);
    }

    // Validate new password input
    if (!Password::validateNew($newPassword)) {
      HttpUtils::prepareJsonResponse($response, [], "New password invalid", 'new-password-invalid');
      return $response->withStatus(401);
    }

    $this->userService->updateById($user->getId(), [
      'password' => Password::hash($newPassword),
    ]);


    // TODO: Send email that notifies about password change

    return $response;
  }

  /**
   * Finalizing of password renewal via token
   */
  #[AsApiRoute('POST', '/profile/finalize-password-rest')]
  public function finalizePasswordReset(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    $params = $request->getParsedBody();
    $token = $params['token'];
    $newPassword = $params['new_password'];
    $decodedPayload = $this->jwt->validate($token);

    if (!$decodedPayload) {
      return $response->withStatus(401);
    }

    $decodedArray = (array) $decodedPayload;

    try {
      $user = $this->userService->getOneById($decodedArray['id']);
    } catch (\Exception $e) {
      return $response->withStatus(404);
    }

    $this->userService->updateById($user->getId(), [
      'password' => Password::hash($newPassword),
      'state' => UserState::ACTIVE,
    ]);

    return $response;
  }

  // TODO
  #[AsApiRoute('GET', '/profile/request-email-change')]
  public function requestEmailChange(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    return $response;
  }

  // TODO
  #[AsApiRoute('POST', '/profile/finalize-email-change')]
  public function finalizeEmailChange(
    ServerRequestInterface $request,
    ResponseInterface $response
  ): ResponseInterface {
    return $response;
  }
}
