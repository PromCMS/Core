<?php

namespace PromCMS\Core\Database\Models\Trait;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PromMapping;

trait Localized
{
  #[ORM\Column(name: 'is_published', type: 'boolean')]
  #[PromMapping\PromModelColumn(title: 'Is published', type: 'boolean')]
  protected bool $published = false;

}