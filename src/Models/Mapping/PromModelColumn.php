<?php

declare(strict_types=1);

namespace PromCMS\Core\Models\Mapping;

use Attribute;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class PromModelColumn
{

  /**
   * @var string
   * @readonly
   */
  public $title;

  /**
   * @var string
   * @readonly
   */
  public $type;

  /**
   * @var boolean
   * @readonly
   */
  public $editable;

  /**
   * @var boolean
   * @readonly
   */
  public $hide;

  /**
   * @var boolean
   * @readonly
   */
  public $localized;

  public function __construct(
    string $title,
    string $type,
    ?bool $editable = true,
    ?bool $hide = false,
    ?bool $localized = false,
  ) {
    $this->title = $title;
    $this->type = $type;
    $this->hide = $hide;
    $this->editable = $editable;
    $this->localized = $localized;
  }
}
