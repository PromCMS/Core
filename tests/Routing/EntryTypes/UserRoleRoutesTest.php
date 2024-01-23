<?php

use DI\Container;
use PromCMS\Core\App;
use PromCMS\Core\Utils\ObjectUtils;
use PromCMS\Tests\AppTestCase;

final class UserRoleRoutesTest extends AppTestCase
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
    $request = $this->createRequest('GET', '/api/entry-types/userRoles/items');
    $response = static::$app->getSlimApp()->handle($request);

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testAuthorizedRequestDoesNotFail()
  {
    $request = $this->createRequest('GET', '/api/entry-types/userRoles/items/admin');

    $newUser = $this->createUser();
    $this->logUserIn($newUser);

    $response = static::$app->getSlimApp()->handle($request);
    $bodyAsString = $response->getBody()->__toString();

    $this->assertEquals(200, $response->getStatusCode());

    $this->assertEqualsCanonicalizing(
      [
        "data" => [
          "id" => "admin",
          "name" => "Admin",
          "slug" => "admin",
          'description' => 'Main user role provided by PromCMS Core module',
          'permissions' => [
            "hasAccessToAdmin" => true,
            "entities" => [
              [
                "c" => "allow-all",
                "r" => "allow-all",
                "u" => "allow-all",
                "d" => "allow-all"
              ],
              [
                "c" => "allow-all",
                "r" => "allow-all",
                "u" => "allow-all",
                "d" => "allow-all"
              ],
              [
                "c" => "allow-all",
                "r" => "allow-all",
                "u" => "allow-all",
                "d" => "allow-all"
              ],
              [
                "c" => "allow-all",
                "r" => "allow-all",
                "u" => "allow-all",
                "d" => "allow-all"
              ]
            ]
          ]
        ],
        "message" => "",
        "code" => false
      ],
      ObjectUtils::objectToArrayRecursive(json_decode($bodyAsString))
    );
  }
}
