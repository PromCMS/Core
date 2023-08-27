<?php

namespace PromCMS\Tests\Services;

use PromCMS\Core\Database\Model;
use PromCMS\Core\Database\Query;
use PromCMS\Tests\AppTestCase;

class CustomModel extends Model
{
  protected string $tableName = 'posts';

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

    'name' => [
      'title' => 'Description',
      'hide' => false,
      'required' => false,
      'unique' => false,
      'editable' => true,
      'translations' => true,
      'type' => 'text',
    ],
  ];
}

final class QueryTest extends AppTestCase
{
  public function testShouldCorrectlyFormatFieldNamesFromWhereToLocalizedFields()
  {
    $model = new CustomModel();
    $fieldName = $model->getInternationalizedFields()[1][0];
    $fieldValue = "value";

    $firstWhere = $model
      ->query()
      ->where([$fieldName, "=", $fieldValue])
      ->getQueryBuilder()
      ->_getConditionProperties()["whereConditions"];

    $secondWhere = $model
      ->query()
      ->setLanguage("cs")
      ->where([[$fieldName, "=", $fieldValue], ["id", "=", 0]])
      ->getQueryBuilder()
      ->_getConditionProperties()["whereConditions"];

    $this->assertEqualsCanonicalizing(
      $firstWhere,
      [[Query::$TRANSLATIONS_FIELD_NAME . ".en.$fieldName", "=", $fieldValue]]
    );

    $this->assertEqualsCanonicalizing(
      $secondWhere,
      [
        [Query::$TRANSLATIONS_FIELD_NAME . ".cs.$fieldName", "=", $fieldValue],
        ["id", "=", 0]
      ]
    );
  }
}
