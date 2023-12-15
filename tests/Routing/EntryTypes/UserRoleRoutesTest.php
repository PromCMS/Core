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
    $request = $this->createRequest('GET', '/api/entry-types/userRoles');
    $response = static::$app->getSlimApp()->handle($request);

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testAuthorizedRequestDoesNotFail()
  {
    $request = $this->createRequest('GET', '/api/entry-types/userRoles');

    $newUser = $this->createUser();
    $this->logUserIn($newUser);

    $response = static::$app->getSlimApp()->handle($request);
    $bodyAsString = $response->getBody()->__toString();

    $this->assertEquals(200, $response->getStatusCode());

    $this->assertEqualsCanonicalizing([
      "data" => [
        "adminMetadata" => [
          "icon" => "UserExclamation"
        ],
        "ignoreSeeding" => false,
        "icon" => "UserExclamation",
        "admin" => [
          "icon" => "UserExclamation"
        ],
        "tableName" => "prom__user_roles",
        "hasTimestamps" => false,
        "hasSoftDelete" => false,
        "columns" => [
          "id" => [
            "editable" => false,
            "hide" => false,
            "title" => "ID",
            "type" => "number",
            "required" => true,
            "unique" => false,
            "translations" => false,
            "autoIncrement" => true
          ],
          "label" => [
            "editable" => true,
            "hide" => false,
            "title" => "Label",
            "type" => "string",
            "required" => true,
            "unique" => false,
            "translations" => false,
            "autoIncrement" => false
          ],
          "description" => [
            "editable" => true,
            "hide" => false,
            "title" => "Description",
            "type" => "string",
            "required" => false,
            "unique" => false,
            "translations" => false,
            "autoIncrement" => false
          ],
          "permissions" => [
            "editable" => true,
            "hide" => false,
            "title" => "Permissions",
            "type" => "json",
            "required" => false,
            "unique" => false,
            "translations" => false,
            "autoIncrement" => false
          ],
          "slug" => [
            "required" => false,
            "unique" => false,
            "translations" => false,
            "autoIncrement" => false
          ]
        ],
        "hasOrdering" => false,
        "isDraftable" => false,
        "isSharable" => false,
        "ownable" => false
      ],
      "message" => "",
      "code" => false
    ],
      ObjectUtils::objectToArrayRecursive(json_decode($bodyAsString))
    );
  }
}
