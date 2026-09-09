<?php

use DI\Container;
use PromCMS\Core\App;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Models\Base\UserState;
use PromCMS\Core\Database\Models\Setting;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Internal\Bootstrap\Database;
use PromCMS\Core\Internal\Bootstrap\Services;
use PromCMS\Core\PromConfig;
use PromCMS\Tests\AppTestCase;
use PromCMS\Tests\TestUtils;
use Symfony\Component\Filesystem\Path;

final class OwnOnlyPermissionRoutesTest extends AppTestCase
{
  static string $testProjectRoot;
  static App $app;
  static Container $container;

  public static function setUpBeforeClass(): void
  {
    static::$projectRoot = Path::join(__DIR__, '..', '..');
    static::$testProjectRoot = Path::join(static::$projectRoot, '.test');
    TestUtils::prepareSystemForTests(static::$testProjectRoot);

    $configPath = Path::join(static::$testProjectRoot, '.prom-cms', 'parsed', 'config.php');
    $config = require $configPath;
    $config['project']['security'] = ['roles' => [[
      'name' => 'Editor',
      'slug' => 'editor',
      'modelPermissions' => [
        'prom__settings' => 'allow-own',
      ],
    ]]];
    file_put_contents($configPath, "<?php\n\nreturn " . var_export($config, true) . ";\n");

    static::$app = new App(static::$testProjectRoot);
    static::$app->init(true);
    static::$container = static::$app->getSlimApp()->getContainer();
    static::$container->set(PromConfig::class, PromConfig::fromProjectRoot(static::$testProjectRoot));
    (new Database())->run(static::$app->getSlimApp(), static::$container);
    (new Services())->run(static::$app->getSlimApp(), static::$container);
  }

  private function logIn(User $user): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $_SESSION['user_id'] = $user->getId();
  }

  public function testAllowOwnRestrictsEntityAccess(): void
  {
    $suffix = bin2hex(random_bytes(4));
    $em = static::$container->get(EntityManager::class);
    $owner = (new User())
      ->setName('Owner User')
      ->setEmail("owner-$suffix@example.test")
      ->setPassword('test1234')
      ->setRole('editor')
      ->setState(UserState::ACTIVE);
    $other = (new User())
      ->setName('Other User')
      ->setEmail("other-$suffix@example.test")
      ->setPassword('test1234')
      ->setRole('editor')
      ->setState(UserState::ACTIVE);
    $missingRole = (new User())
      ->setName('Missing Role')
      ->setEmail("missing-$suffix@example.test")
      ->setPassword('test1234')
      ->setRole('missing')
      ->setState(UserState::ACTIVE);
    $setting = (new Setting())
      ->setName("Owned setting $suffix")
      ->setSlug("owned-setting-$suffix")
      ->setContent([]);
    $setting->setCreatedBy($owner);
    $em->persist($owner);
    $em->persist($other);
    $em->persist($missingRole);
    $em->persist($setting);
    $em->flush();

    $this->logIn($other);
    $id = $setting->getId();
    $list = static::$app->getSlimApp()->handle($this->createRequest('GET', '/api/entry-types/prom__settings/items'));
    $get = static::$app->getSlimApp()->handle($this->createRequest('GET', "/api/entry-types/prom__settings/items/$id"));
    $patch = static::$app->getSlimApp()->handle($this->createJsonRequest('PATCH', "/api/entry-types/prom__settings/items/$id", [
      'data' => ['name' => "Changed setting $suffix"],
    ]));
    $delete = static::$app->getSlimApp()->handle($this->createRequest('DELETE', "/api/entry-types/prom__settings/items/$id"));
    $em->refresh($setting);

    $this->assertSame(200, $list->getStatusCode());
    $this->assertStringNotContainsString($setting->getSlug(), (string) $list->getBody());
    $this->assertSame(404, $get->getStatusCode());
    $this->assertSame(404, $patch->getStatusCode());
    $this->assertSame(404, $delete->getStatusCode());
    $this->assertSame("Owned setting $suffix", $setting->getName());

    $this->logIn($owner);
    $this->assertSame(200, static::$app->getSlimApp()->handle($this->createRequest('GET', "/api/entry-types/prom__settings/items/$id"))->getStatusCode());

    $this->logIn($missingRole);
    $this->assertSame(401, static::$app->getSlimApp()->handle($this->createRequest('GET', '/api/entry-types/prom__settings/items'))->getStatusCode());
  }
}
