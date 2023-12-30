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
   * @var string
   * @readonly
   */
  public $adminMetadataIcon;

  /**
   * @var boolean
   * @readonly
   */
  public $ignoreSeeding;

  public function __construct(string $adminMetadataIcon, ?bool $ignoreSeeding = false)
  {
    $this->adminMetadataIcon = $adminMetadataIcon;
    $this->ignoreSeeding = $ignoreSeeding;
  }
}