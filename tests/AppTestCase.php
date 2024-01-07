<?php

namespace PromCMS\Tests;

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Models\Base\UserState;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Module;
use PromCMS\Core\Password;
use PromCMS\Core\Utils\FsUtils;
use PromCMS\Tests\TestUtils;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Factory\StreamFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Uri;
use PromCMS\Core\App;
use Symfony\Component\Filesystem\Path;

abstract class AppTestCase extends TestCase
{
  static string $projectRoot;
  static string $testProjectRoot;
  static App $app;
  static \Faker\Generator $faker;

  // Setup folder and core files
  public static function setUpBeforeClass(): void
  {
    static::$projectRoot = Path::join(__DIR__, "..");
    static::$testProjectRoot = Path::join(static::$projectRoot, ".test");

    TestUtils::prepareSystemForTests(static::$testProjectRoot);
    TestUtils::ensureSession();

    static::$app = new App(static::$testProjectRoot);
    static::$app->init(true);
    TestUtils::ensureEmptyDatabase(static::$app);
    static::$faker = Factory::create();
  }

  public function setUp(): void
  {
    TestUtils::clearSession();
  }

  public static function tearDownAfterClass(): void
  {
    TestUtils::generalCleanup(static::$testProjectRoot);
  }

  function createModule(string $moduleName, array $otherData = null)
  {
    $moduleRoot = Path::join(Module::$modulesRoot, $moduleName);
    mkdir($moduleRoot);

    file_put_contents(Path::join($moduleRoot, Module::$moduleInfoFileName), json_encode(array_merge([
      "name" => $moduleName
    ], $otherData ?? [])));
  }

  function deleteAllModules()
  {
    FsUtils::rrmdir(Module::$modulesRoot);

    mkdir(Module::$modulesRoot);
  }

  function getContainer()
  {
    return static::$app->getSlimApp()->getContainer();
  }

  function createUser(array $overrides = [])
  {
    $autorizedUser = new User();
    $autorizedUser->setName($overrides['name'] ?? static::$faker->name());
    $autorizedUser->setEmail($overrides['email'] ?? static::$faker->email());
    $autorizedUser->setPassword(Password::hash($overrides['password'] ?? 'test1234'));
    // $autorizedUser->setRoleId(0);
    $autorizedUser->setRoleSlug('admin');
    $autorizedUser->setState($overrides['state'] ?? UserState::ACTIVE);

    $em = $this->getContainer()->get(EntityManager::class);
    $em->persist($autorizedUser);
    $em->flush();

    return $autorizedUser;
  }

  function logUserIn(User $user)
  {
    $_SESSION["user_id"] = $user->getId();
  }

  /**
   * @param string $method
   * @param string $path
   * @param array  $headers
   * @param array  $cookies
   * @param array  $serverParams
   * @return Request
   */
  protected function createRequest(
    string $method,
    string $path,
    array $headers = ['HTTP_ACCEPT' => 'application/json'],
    array $cookies = [],
    array $serverParams = []
  ): Request {
    $uri = new Uri('', '', 80, $path);
    $handle = fopen('php://temp', 'w+');
    $stream = (new StreamFactory())->createStreamFromResource($handle);

    $h = new Headers();
    foreach ($headers as $name => $value) {
      $h->addHeader($name, $value);
    }

    return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
  }

  /**
   * Create a JSON request.
   *
   * @param string $method The HTTP method
   * @param string|\Psr\Http\Message\UriInterface $uri The URI
   * @param array|null $data The json data
   *
   * @return \Psr\Http\Message\ServerRequestInterface
   */
  protected function createJsonRequest(string $method, $uri, array $data = null): ServerRequestInterface
  {
    $request = $this->createRequest($method, $uri);

    if ($data !== null) {
      $request->getBody()->write((string) json_encode($data));
    }

    return $request->withHeader('Content-Type', 'application/json');
  }
}
