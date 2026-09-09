<?php

namespace PromCMS\Core\PromConfig\Entity;

use PromCMS\Core\PromConfig;

class Column
{
  public array $otherMetadata;

  public function __construct(
    public readonly string $name,
    public readonly string $type,
    public readonly string $title,
    public readonly PromConfig $promConfig,
    public readonly bool $required = true,
    public readonly bool|string $unique = false,
    public readonly bool $localized = false,
    public readonly bool $readonly = false,
    public readonly bool $hide = false,
    public readonly ?string $defaultValue = null,
    public readonly bool $identifier = false,
    public array $admin = [],
    public array $database = [],
    ...$other
  ) {
    $this->admin = array_merge(
      [
        'isHidden' => false,
      ],
      $this->admin
    );

    $this->admin['editor'] = array_merge(
      [
        'width' => 12,
        'placement' => 'aside',
      ],
      $this->admin['editor'] ?? []
    );

    $this->otherMetadata = $other;
  }

  function getDatabaseColumName()
  {
    if ($this->database['columnName'] ?? false) {
      return $this->database['columnName'];
    }

    $name = strtolower(preg_replace('~(?=[A-Z])(?!\A)~', '_', $this->name));

    return $name;
  }

  function isEnumColumn()
  {
    return $this->type === 'enum';
  }

  function isPrimaryKey()
  {
    return $this->primaryKey;
  }

  function getDoctrineType()
  {
    return match ($this->type) {
      'boolean' => 'boolean',
      'json' => 'array',
      'longText' => 'text',
      'password' => 'text',
      'date' => 'date',
      'dateTime' => 'datetime',
      'number' => 'integer',
      'file' => 'integer',
      'relationship' => 'integer',
      default => 'string',
    };
  }
  function getPhpType()
  {
    if ($this->isEnumColumn()) {
      return $this->otherMetadata['enum']['name'];
    }

    return match ($this->type) {
      'boolean' => 'bool',
      'number' => 'int',
      'json' => 'array',
      'date' => '\DateTimeInterface',
      'dateTime' => '\DateTimeInterface',
      default => 'string',
    };
  }
}
