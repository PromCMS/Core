<?php

namespace PromCMS\Core\Models;

use PromCMS\Core\Database\Model;

class GeneralTranslations extends Model
{
  protected string $tableName = 'generaltranslations';
  protected bool $timestamps = false;
  protected bool $softDelete = false;
  protected bool $translations = false;

  public static array $tableColumns = [
    'id' => [
      'required' => false,
      'editable' => false,
      'unique' => true,
      'hide' => false,
      'translations' => false,
      'autoIncrement' => true,
      'title' => 'ID',
      'type' => 'number',
    ],

    'lang' => [
      'required' => true,
      'editable' => true,
      'unique' => false,
      'hide' => false,
      'translations' => true,
      'title' => 'Language',
      'type' => 'string',
    ],

    'key' => [
      'required' => true,
      'editable' => true,
      'unique' => false,
      'hide' => false,
      'translations' => true,
      'title' => 'Key',
      'type' => 'string',
    ],

    'value' => [
      'required' => true,
      'editable' => true,
      'unique' => false,
      'hide' => false,
      'translations' => true,
      'title' => 'Value',
      'type' => 'string',
    ],
  ];

  static bool $ignoreSeeding = false;
  static string $modelIcon = 'LanguageHiragana';
  static $adminSettings = [
    'layout' => 'simple',
  ];

  public function getSummary()
  {
    return (object) [
      'icon' => self::$modelIcon,
      'ignoreSeeding' => self::$ignoreSeeding,
      'admin' => self::$adminSettings,
      'tableName' => $this->getTableName(),
      'hasTimestamps' => $this->hasTimestamps(),
      'hasSoftDelete' => $this->hasSoftDelete(),
      'columns' => static::$tableColumns,
      'hasOrdering' => false,
      'isDraftable' => false,
      'isSharable' => false,
      'ownable' => false,
    ];
  }
}
