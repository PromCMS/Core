<?php

namespace PromCMS\Core\Database\Models\Abstract;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping\PromModelColumn;
use PromCMS\Core\Database\Models\Trait\Draftable;
use PromCMS\Core\Database\Models\Trait\Localized;
use PromCMS\Core\Database\Models\Trait\Ordable;
use PromCMS\Core\Database\Models\Trait\Ownable;
use PromCMS\Core\Database\Models\Trait\Sharable;
use PromCMS\Core\Database\Models\Trait\Timestamps;
use PromCMS\Core\PromConfig;

abstract class Entity
{
  private array $cachedMetadata;

  /**
   * returns metadata for current model, provide prom config to also include some data from prom config
   */
  protected function getMetadata(PromConfig $promConfig)
  {
    if ($this->cachedMetadata) {
      return $this->cachedMetadata;
    }

    $ref = new \ReflectionClass(static::class);
    $attributes = $ref->getAttributes();
    $tableName = '';

    foreach ($attributes as $attribute) {
      if ($attribute->getName() === ORM\Table::class) {
        $tableName = $attribute->getArguments()['name'];
        break;
      }
    }

    $usedTraits = $ref->getTraits() ?? [];
    $metadata = [
      // TODO: Implement archives
      'hasSoftDelete' => false,
      'ownable' => in_array(Ownable::class, $usedTraits),
      'hasTimestamps' => in_array(Timestamps::class, $usedTraits),
      'hasOrdering' => in_array(Ordable::class, $usedTraits),
      'isDraftable' => in_array(Draftable::class, $usedTraits),
      'isSharable' => in_array(Sharable::class, $usedTraits),
      'isLocalized' => in_array(Localized::class, $usedTraits),
      'columns' => $promConfig->getTableColumns($tableName)
    ];

    return $this->cachedMetadata = $metadata;
  }

  /**
   * @return array<string, \ReflectionProperty>
   */
  protected function getPromFields()
  {
    $ref = new \ReflectionClass(static::class);
    $propers = $ref->getProperties();
    $res = [];

    /**
     * @var \ReflectionProperty  $proper
     */
    foreach ($propers as $proper) {
      /**
       * @var \ReflectionAttribute  $attr
       */
      if (!empty($proper->getAttributes(PromModelColumn::class)[0])) {
        $res[$proper->getName()] = $proper;
      }
    }

    return $res;
  }

  public function toArray()
  {
    $propers = $this->getPromFields();
    $res = [];

    foreach ($propers as $proper) {
      /** @var \ReflectionAttribute */
      $attr = $proper->getAttributes(PromModelColumn::class)[0];
      $propertyName = $proper->getName();

      if (!isset($this->{$propertyName})) {
        continue;
      }

      /** @var PromModelColumn */
      $info = $attr->newInstance();

      if (!$info->hide) {
        $value = $this->{$propertyName};

        if ($value instanceof Entity) {
          $value = $value->toArray();
        } else if ($value instanceof \DateTimeInterface) {
          $value = $value->format(\DateTime::ISO8601);
        } else if ($value instanceof Collection) {
          $result = [];

          foreach ($value as $row) {
            $result[] = ['id' => $row->getId()];
          }

          $value = $result;
        }

        $res[$propertyName] = $value;
      }
    }

    return $res;
  }


  /**
   * Compares incomming values with existing fields and returns filtered columns with PromModelColumn instances
   * 
   *@return array<PromModelColumn>
   */
  protected function getColumnsForValues(array $values): array
  {
    $propers = $this->getPromFields();
    $incommingValuesAsKeys = array_keys($values);
    $newValue = [];

    foreach ($propers as $propertyName => $proper) {
      if (!in_array($propertyName, $incommingValuesAsKeys)) {
        continue;
      }

      /** @var \ReflectionAttribute */
      $attr = $proper->getAttributes(PromModelColumn::class)[0];
      if (!$attr) {
        continue;
      }

      /** @var PromModelColumn */
      $info = $attr->newInstance();

      if ($info->title === "Updated at" || $info->title === "Created at") {
        continue;
      }

      $newValue[$propertyName] = $info;
    }

    return $newValue;
  }

  public function fill(array $values)
  {
    $columns = $this->getColumnsForValues($values);

    foreach ($columns as $propertyName => $proper) {
      $incommingValue = $values[$propertyName];

      if (isset($this->{$propertyName}) && $this->{$propertyName} instanceof Collection) {
        if ($proper->type === 'file') {
          $this->{$propertyName}->clear();
        }

        foreach ($incommingValue as $value) {
          if (!$this->{$propertyName}->contains($value)) {
            $this->{$propertyName}->add($value);
          }
        }

        continue;
      }

      if ($incommingValue && $proper->type === "enum" && is_string($incommingValue)) {
        $proper = new \ReflectionProperty(static::class, $propertyName);
        $types = $proper->getType() instanceof \ReflectionUnionType ? $proper->getTypes() : [$proper->getType()];

        foreach ($types as $type) {
          if (enum_exists($type->getName())) {
            $incommingValue = ($type->getName())::from($incommingValue);
          }
        }
      }

      $this->{$propertyName} = $incommingValue;
    }

    return $this;
  }
}