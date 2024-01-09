<?php

namespace PromCMS\Core\Database\Models\Abstract;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping\PromModelColumn;
use PromCMS\Core\Database\Models\Trait\Draftable;
use PromCMS\Core\Database\Models\Trait\Localized;
use PromCMS\Core\Database\Models\Trait\Ordable;
use PromCMS\Core\Database\Models\Trait\Ownable;
use PromCMS\Core\Database\Models\Trait\Sharable;
use PromCMS\Core\Database\Models\Trait\Timestamps;
use PromCMS\Core\PromConfig;

abstract class BaseModel
{
  private array $cachedMetadata;

  /**
   * returns metadata for current model, provide prom config to also include some data from prom config
   */
  private function getMetadata(PromConfig $promConfig)
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
  private function getPromFields()
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
      /** @var PromModelColumn */
      $info = $attr->newInstance();
      $propertyName = $proper->getName();

      if (!isset($this->{$propertyName})) {
        continue;
      }

      if (!$info->hide) {
        $value = $this->{$propertyName};
        $res[$propertyName] = $value;
      }
    }

    return $res;
  }

  public function fill(array $values)
  {
    $propers = $this->getPromFields();
    $incommingValuesAsKeys = array_keys($values);

    foreach ($propers as $propertyName => $proper) {
      if (!in_array($propertyName, $incommingValuesAsKeys)) {
        continue;
      }

      $attr = $proper->getAttributes(PromModelColumn::class)[0];
      $info = new PromModelColumn(...$attr->getArguments());

      if ($info->editable) {
        $this->{$propertyName} = $values[$propertyName];
      }
    }

    return $this;
  }
}