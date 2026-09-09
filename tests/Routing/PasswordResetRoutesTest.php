<?php

use DI\Container;
use PromCMS\Core\App;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Models\Base\UserState;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Internal\Bootstrap\Database;
use PromCMS\Core\Internal\Bootstrap\Services;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Services\JWTService;
use PromCMS\Tests\AppTestCase;
use PromCMS\Tests\TestUtils;
use Symfony\Component\Filesystem\Path;

final class PasswordResetRoutesTest extends AppTestCase
{
  static string $testProjectRoot;
  static App $app;
  static Container $container;

  public static function setUpBeforeClass(): void
  {
    static::$projectRoot = Path::join(__DIR__, '..', '..');
    static::$testProjectRoot = Path::join(static::$projectRoot, '.test');
    TestUtils::prepareSystemForTests(static::$testProjectRoot);

    static::$app = new App(static::$testProjectRoot);
    static::$app->init(true);
    static::$container = static::$app->getSlimApp()->getContainer();
    static::$container->set(PromConfig::class, PromConfig::fromProjectRoot(static::$testProjectRoot));
    (new Database())->run(static::$app->getSlimApp(), static::$container);
    (new Services())->run(static::$app->getSlimApp(), static::$container);
  }

  private function createUser(UserState $state, string $email): User
  {
    $user = (new User())
      ->setName('Reset User')
      ->setEmail($email)
      ->setPassword('oldpassword')
      ->setRole('admin')
      ->setState($state);
    $em = static::$container->get(EntityManager::class);
    $em->persist($user);
    $em->flush();

    return $user;
  }

  private function finalize(string $token, string $password)
  {
    $request = $this->createJsonRequest('POST', '/api/profile/finalize-password-rest', [
      'token' => $token,
      'new_password' => $password,
    ]);

    return static::$app->getSlimApp()->handle($request);
  }

  public function testResetIsSingleUseAndRejectsInvalidStatesAndPasswords(): void
  {
    $suffix = bin2hex(random_bytes(4));
    $em = static::$container->get(EntityManager::class);
    $jwt = static::$container->get(JWTService::class);
    $resetUser = $this->createUser(UserState::PASSWORD_RESET, "reset-$suffix@example.test");
    $resetToken = $jwt->generate(['id' => $resetUser->getId()]);

    $this->assertSame(200, $this->finalize($resetToken, 'newpass')->getStatusCode());
    $em->refresh($resetUser);
    $this->assertSame(UserState::ACTIVE, $resetUser->getState());
    $this->assertTrue($resetUser->checkPassword('newpass'));
    $this->assertSame(401, $this->finalize($resetToken, 'anotherpass')->getStatusCode());

    $activeUser = $this->createUser(UserState::ACTIVE, "active-$suffix@example.test");
    $this->assertSame(401, $this->finalize($jwt->generate(['id' => $activeUser->getId()]), 'newpass')->getStatusCode());
    $em->refresh($activeUser);
    $this->assertTrue($activeUser->checkPassword('oldpassword'));

    $shortUser = $this->createUser(UserState::PASSWORD_RESET, "short-$suffix@example.test");
    $this->assertSame(401, $this->finalize($jwt->generate(['id' => $shortUser->getId()]), 'a     b')->getStatusCode());
    $em->refresh($shortUser);
    $this->assertSame(UserState::PASSWORD_RESET, $shortUser->getState());
    $this->assertTrue($shortUser->checkPassword('oldpassword'));
  }
}
