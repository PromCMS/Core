<?php

namespace PromCMS\Core\Models\Trait;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as PromMapping;

trait Localized
{
  #[ORM\Column(name: 'is_published', type: 'boolean')]
  #[PromMapping\PromModelColumn(title: 'Is published', type: 'boolean')]
  protected bool $published = false;

}