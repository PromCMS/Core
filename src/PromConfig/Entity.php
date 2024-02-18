<?php

namespace PromCMS\Core\PromConfig;

use PromCMS\Core\Database\Models\Trait\Draftable;
use PromCMS\Core\Database\Models\Trait\Localized;
use PromCMS\Core\Database\Models\Trait\NumericId;
use PromCMS\Core\Database\Models\Trait\Ordable;
use PromCMS\Core\Database\Models\Trait\Ownable;
use PromCMS\Core\Database\Models\Trait\Sharable;
use PromCMS\Core\Database\Models\Trait\Timestamps;
use PromCMS\Core\PromConfig;
use PromCMS\Core\PromConfig\Entity\Column;
use PromCMS\Core\PromConfig\Entity\RelationshipColumn;

class Entity
{
  public string $className;
  public readonly bool $localized;
  public array $traits = [];

  private function initializeTraits()
  {

    if ($this->timestamp) {
      $this->traits[] = Timestamps::class;
    }

    if ($this->sorting) {
      $this->traits[] = Ordable::class;
    }

    if ($this->ownable) {
      $this->traits[] = Ownable::class;
    }

    if ($this->draftable) {
      $this->traits[] = Draftable::class;
    }

    $localizedFields = array_filter($this->columns, fn($column) => $column['localized']);
    if (count($localizedFields)) {
      $this->traits[] = Localized::class;
    }

    if ($this->sharable) {
      $this->traits[] = Sharable::class;
    }

    $this->traits[] = NumericId::class;
  }

  public function __construct(
    public readonly string $title,
    public readonly string $tableName,
    public readonly array $columns,
    public readonly string $namespace,
    private readonly PromConfig $promConfig,
    public ?string $phpName = null,
    public readonly bool $timestamp = true,
    public readonly bool $sorting = false,
    public readonly bool $draftable = false,
    public readonly bool $softDelete = false,
    public readonly bool $sharable = false,
    public readonly bool $ownable = false,
    public readonly bool $ignoreSeeding = false,
    public array $admin = [],
    public readonly bool $partOfCore = false,
    ...$rest
  ) {
    $this->admin = array_merge_recursive(['isHidden' => false], $this->admin);
    $this->phpName = $this->phpName ?? str_replace('_', '', ucwords($this->tableName, '_'));
    $this->className = $this->namespace . '\\' . $this->phpName;
    $this->initializeTraits();
    $this->localized = in_array(Localized::class, $this->traits);
  }

  /**
   * @return array<int, Column|RelationshipColumn>
   */
  function getPublicColumns(): array
  {
    return array_filter($this->getColumns(), fn(Column|RelationshipColumn $column) => !$column->hide);
  }


  /**
   * @return array<int, Column|RelationshipColumn>
   */
  function getPrivateColumns(): array
  {
    return array_filter($this->getColumns(), fn(Column|RelationshipColumn $column) => $column->hide);
  }

  function isSingleton()
  {
    return in_array($this->tableName, $this->promConfig->getSingletonTableNames());
  }

  private ?array $cachedColumnsAsInstances = null;
  /**
   * @return array<int, Column|RelationshipColumn>
   */
  function getColumns()
  {
    if ($this->cachedColumnsAsInstances) {
      return $this->cachedColumnsAsInstances;
    }

    foreach ($this->columns as $column) {
      $column['promConfig'] = $this->promConfig;

      $columnInstance = match ($column['type']) {
        'relationship' => new Entity\RelationshipColumn(...$column),
        'file' => new Entity\FileColumn(...$column),
        default => new Entity\Column(...$column)
      };

      $this->cachedColumnsAsInstances[] = $columnInstance;
    }

    return $this->cachedColumnsAsInstances;
  }

  /**
   * @return array<int, RelationshipColumn>
   */
  function getRelationshipColumns()
  {
    return array_filter($this->getColumns(), fn(Column|RelationshipColumn $column) => $column instanceof RelationshipColumn);
  }

  function getColumnByName(string $name)
  {
    foreach ($this->getColumns() as $column) {
      if ($column->name === $name) {
        return $column;
      }
    }

    return null;
  }

  /**
   * @return array<int, Column>
   */
  function getEnumColumns()
  {
    return array_filter($this->getColumns(), fn(Column|RelationshipColumn $column) => $column->isEnumColumn());
  }

  function getLocalizedColumns()
  {
    return array_filter($this->getColumns(), fn(Column|RelationshipColumn $column) => $column->localized);
  }

  function getTranslationTableName(): ?string
  {
    if (!$this->localized) {
      return null;
    }

    return $this->tableName . '_translations';
  }

  function getTranslationPhpName(): ?string
  {
    if (!$this->localized) {
      return null;
    }

    return $this->phpName . 'Translation';
  }

  function getTranslationClassName(): ?string
  {
    if (!$this->localized) {
      return null;
    }

    return $this->className . 'Translation';
  }
}