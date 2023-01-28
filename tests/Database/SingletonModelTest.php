<?php

namespace PromCMS\Tests\Services;

use PromCMS\Core\Database\Query;
use PromCMS\Core\Database\SingletonModel;
use PromCMS\Tests\AppTestCase;

class CustomSingleton extends SingletonModel
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

final class SingletonModelTest extends AppTestCase
{
  public function testShouldCreateCorrectly()
  {
    $customSingletonInstance = new CustomSingleton();
    $payload = [
      Query::$SINGLETON_NAME_FIELD_NAME => $customSingletonInstance->getName(),
      'description' => "this is a description"
    ];
    $res = $customSingletonInstance->query()->create($payload);

    $this->assertEqualsCanonicalizing(
      array_merge(
        $payload,
        [
          // It will be 1 every time since we don't have any data
          "id" => 1
        ]
      ),
      $res->getData()
    );
  }
}
