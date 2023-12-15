<?php

use DI\Container;
use PromCMS\Core\App;
use PromCMS\Tests\AppTestCase;

final class ProfileRoutesTest extends AppTestCase
{
  static string $testProjectRoot;
  static App $app;
  static Container $container;

  public static function setUpBeforeClass(): void
  {
    parent::setUpBeforeClass();

    static::$container = static::$app->getSlimApp()->getContainer();
  }

  public function setUp(): void
  {
    parent::setUp();
  }

  public function testUnauthorizedRequestFailsWith401()
  {
    $request = $this->createRequest('GET', '/api/profile/me');
    $response = static::$app->getSlimApp()->handle($request);

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testSuccessfullyAuthorizes()
  {
    $this->createUser([
      "email" => "test@example.com"
    ]);

    $request = $this->createJsonRequest('POST', '/api/profile/login', [
      "email" => 'test@example.com',
      'password' => 'test1234'
    ]);

    $response = static::$app->getSlimApp()->handle($request);
    $responseAsArray = (array) json_decode($response->getBody()->__toString());

    $this->assertEquals(200, $response->getStatusCode());

    $responseKeys = ['result', 'data', 'message'];

    foreach ($responseKeys as $responseKey) {
      $this->assertArrayHasKey($responseKey, $responseAsArray);
    }
  }


  // public function testAuthorizedRequestDoesNotFail()
  // {
  //   $request = $this->createRequest('GET', '/api/entry-types/users/items');
  //   $newUser = $this->createUser();

  //   $this->logUserIn($newUser);

  //   $response = static::$app->getSlimApp()->handle($request);
  //   $bodyAsString = $response->getBody()->__toString();
  //   $body = (array) json_decode($bodyAsString);
  //   $expectedKeys = ['data', 'last_page', 'per_page', 'total', 'from', 'to'];

  //   $this->assertEquals(200, $response->getStatusCode());
  //   $this->assertEquals($expectedKeys, array_keys($body));

  //   foreach (User::getPrivateFields() as $privateField) {
  //     $this->assertStringNotContainsString($privateField, $bodyAsString);
  //   }
  // }
}
