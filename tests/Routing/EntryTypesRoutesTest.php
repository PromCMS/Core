<?php

use DI\Container;
use PromCMS\Core\App;
use PromCMS\Tests\AppTestCase;

final class EntryTypesRoutesTest extends AppTestCase
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
    $request = $this->createRequest('GET', '/api/entry-types');
    $response = static::$app->getSlimApp()->handle($request);

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testAuthorizedRequestDoesNotFail()
  {
    $request = $this->createRequest('GET', '/api/entry-types');

    $newUser = $this->createUser();
    $this->logUserIn($newUser);

    $response = static::$app->getSlimApp()->handle($request);

    $this->assertEquals(200, $response->getStatusCode());
  }
}