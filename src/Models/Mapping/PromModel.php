<?php

declare(strict_types=1);

namespace PromCMS\Core\Models\Mapping;

use Attribute;

/**
 * @Annotation
 * @Target("CLASS")
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class PromModel
{
  /**
   * @var boolean
   * @readonly
   */
  public $ignoreSeeding;

  public function __construct(?bool $ignoreSeeding = false)
  {
    $this->ignoreSeeding = $ignoreSeeding;
  }
}