<?php

namespace PromCMS\Core\Models;

use PromCMS\Core\Database\Model;

class Users extends Model
{
  protected string $tableName = 'users';
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

    'name' => [
      'required' => true,
      'editable' => true,
      'unique' => false,
      'hide' => false,
      'translations' => true,
      'title' => 'Name',
      'type' => 'string',
      'admin' => ['isHidden' => false, 'editor' => ['placement' => 'main']],
    ],

    'password' => [
      'required' => true,
      'editable' => true,
      'unique' => false,
      'hide' => true,
      'translations' => true,
      'title' => 'Password',
      'type' => 'password',
    ],

    'email' => [
      'required' => true,
      'editable' => true,
      'unique' => true,
      'hide' => false,
      'translations' => true,
      'title' => 'Email',
      'type' => 'string',
      'admin' => ['isHidden' => false, 'editor' => ['placement' => 'main']],
    ],

    'avatar' => [
      'required' => false,
      'editable' => true,
      'unique' => false,
      'hide' => false,
      'translations' => true,
      'title' => 'Avatar',
      'type' => 'string',
      'admin' => ['isHidden' => false, 'editor' => ['placement' => 'main']],
    ],

    'state' => [
      'required' => true,
      'editable' => false,
      'unique' => false,
      'hide' => false,
      'translations' => true,
      'title' => 'State',
      'type' => 'enum',
      'enum' => ['active', 'invited', 'blocked', 'password-reset'],
      'admin' => ['isHidden' => false, 'editor' => ['placement' => 'main']],
    ],

    'role' => [
      'required' => true,
      'editable' => true,
      'unique' => false,
      'hide' => false,
      'translations' => true,
      'multiple' => false,
      'foreignKey' => 'id',
      'fill' => false,
      'type' => 'relationship',
      'targetModel' => 'userRoles',
      'title' => 'Role',
      'adminHidden' => true,
      'labelConstructor' => 'label',
      'admin' => ['isHidden' => false, 'editor' => ['placement' => 'main']],
    ],
  ];

  static bool $ignoreSeeding = false;
  static string $modelIcon = 'Users';
  static $adminSettings = [];

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
