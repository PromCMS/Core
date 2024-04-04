<?php

use DI\Container;
use Doctrine\Common\Collections\ArrayCollection;
use PromCMS\Core\App;
use PromCMS\Core\Database\Paginate;
use PromCMS\Core\PromConfig;
use PromCMS\Tests\AppTestCase;

final class UserRoutesTest extends AppTestCase
{
  static string $testProjectRoot;
  static App $app;
  static Container $container;

  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    static::$container = static::$app->getSlimApp()->getContainer();
  }

  public function testUnauthorizedRequestFailsWith401()
  {
    $request = $this->createRequest('GET', '/api/entry-types/users');
    $response = static::$app->getSlimApp()->handle($request);

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testAuthorizedRequestDoesNotFail()
  {
    $request = $this->createRequest('GET', '/api/entry-types/users/items');
    $newUser = $this->mockUser();
    /**
     * @var PromConfig
     */
    $promConfig = static::$app->getSlimApp()->getContainer()->get(PromConfig::class);

    $this->logUserIn($newUser);

    $paginateMock = $this->createMock(Paginate::class);
    $paginateMock
      ->expects($this->any())
      ->method('getTotal')
      ->willReturn(1);

    $paginateMock
      ->expects($this->any())
      ->method('getItems')
      ->willReturn(new ArrayCollection([$newUser]));

    $this->getMockedUserService()
      ->expects($this->any())
      ->method('getManyPaged')
      ->willReturn($paginateMock);

    $response = static::$app->getSlimApp()->handle($request);
    $bodyAsString = $response->getBody()->__toString();
    $body = (array) json_decode($bodyAsString);
    $expectedKeys = ['data', 'current_page', 'last_page', 'total'];

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals($expectedKeys, array_keys($body));
    $entity = $promConfig->getEntity('prom__users');

    if (!$entity) {
      throw new Exception("Users table not in config");
    }

    foreach ($entity->getPrivateColumns() as $privateField) {
      $this->assertStringNotContainsString($privateField->name, $bodyAsString);
    }
  }
}
