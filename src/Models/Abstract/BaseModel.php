<?php

namespace PromCMS\Core\Models\Abstract;

use PromCMS\Core\Models\Mapping as PromMapping;
use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Trait\Draftable;
use PromCMS\Core\Models\Trait\Localized;
use PromCMS\Core\Models\Trait\Ordable;
use PromCMS\Core\Models\Trait\Ownable;
use PromCMS\Core\Models\Trait\Sharable;
use PromCMS\Core\Models\Trait\Timestamps;
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
}