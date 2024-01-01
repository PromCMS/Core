<?php

namespace PromCMS\Core\PromConfig\Entity;

use PromCMS\Core\PromConfig;

class Column {
  public array $otherMetadata;

  public function __construct(
    public readonly string $name,
    public readonly string $type,
    public readonly string $title,
    public readonly PromConfig $promConfig,
    public readonly bool $required = true,
    public readonly bool $unique = false,
    public readonly bool $localized = false,
    public readonly bool $readonly = false,
    public readonly bool $hide = false,
    public readonly ?string $defaultValue = null,
    public array $admin = [],
    ...$other
  ) {
    $this->admin = array_merge([
      'isHidden' => false,
    ], $this->admin);

    $this->admin['editor'] = array_merge([
      'width' => 12,
      'placement' => 'aside'
    ], $this->admin['editor']);

    $this->otherMetadata = $other;
  }

  function getDatabaseColumName() {
    $name = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', strtolower(str_replace(' \t\r\n\f\v', '_', $this->name))));

    return $name;
  }

  function isEnumColumn() {
    return $this->type === 'enum';
  }

  function getDoctrineType() {
    return match($this->type) {
      'boolean' => 'boolean',
      'json' => 'array',
      'longText' => 'text',
      'password' => 'text',
      'date' => 'date',
      'dateTime' => 'datetime',
      'number' => 'integer',
      default => 'string'
    };
  }
  function getPhpType() {
    if ($this->isEnumColumn()) {
      return $this->otherMetadata['enum']['name'];
    }
    
   return match($this->type) {
      'number' => 'int',
      'json' => 'array',
      'date' => '\DateTimeInterface',
      'dateTime' => '\DateTimeInterface',
      default => 'string'
    };
  }
}