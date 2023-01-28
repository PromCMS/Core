<?php

namespace PromCMS\Tests\Services;

use PromCMS\Core\Database\Query;
use PromCMS\Core\Database\SingletonModel;
use PromCMS\Core\Services\SingletonService;
use PromCMS\Tests\AppTestCase;

class PostSingleton extends SingletonModel
{
  protected string $name = 'posts';

  public static array $tableColumns = [
    'id' => [
      'title' => 'ID',
      'hide' => false,
      'required' => false,
      'unique' => true,
      'editable' => false,
      'translations' => false,
      'type' => 'number',
      'autoIncrement' => true,
    ],

    'description' => [
      'title' => 'Description',
      'hide' => false,
      'required' => false,
      'unique' => false,
      'editable' => true,
      'translations' => true,
      'type' => 'longText',
    ],
  ];
}

final class SingletonServiceTest extends AppTestCase
{
  public function testShouldCreateOnGetWhenNotCreated()
  {
    $customSingletonInstance = new PostSingleton();
    $service = new SingletonService($customSingletonInstance);
    $res = $service->getOne([]);

    $this->assertEqualsCanonicalizing(
      [
        Query::$SINGLETON_NAME_FIELD_NAME => $customSingletonInstance->getName(),
        // It will be 1 every time since we don't have any data
        "id" => 1
      ],
      $res->getData()
    );
  }

  public function testShouldCreateOnUpdateWhenIsNotCreated()
  {
    $customSingletonInstance = new PostSingleton();
    $service = new SingletonService($customSingletonInstance);
    $payload = [
      "content" => "foo bar baz"
    ];

    $res = $service->update([], $payload);

    $this->assertEqualsCanonicalizing(
      array_merge([
        Query::$SINGLETON_NAME_FIELD_NAME => $customSingletonInstance->getName(),
        // It will be 1 every time since we don't have any data
        "id" => 1
      ], $payload),
      $res->getData()
    );
  }

  public function testShouldClear()
  {
    $customSingletonInstance = new PostSingleton();
    $service = new SingletonService($customSingletonInstance);
    $payload = [
      "content" => "foo bar baz"
    ];

    $res = $service->update([], $payload);
    $res = $service->clear([]);

    $this->assertEqualsCanonicalizing(
      [
        Query::$SINGLETON_NAME_FIELD_NAME => $customSingletonInstance->getName(),
        // It will be 2 every time since we added and then removed, but the auto-increment still persists
        "id" => 2
      ],
      $res->getData()
    );
  }
}
