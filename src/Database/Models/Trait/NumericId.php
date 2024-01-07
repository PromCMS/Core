<?php

namespace PromCMS\Core\Database\Models\Trait;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PromMapping;

trait NumericId
{
  #[ORM\Id]
  #[ORM\Column(type: 'integer')]
  #[ORM\GeneratedValue]
  #[PromMapping\PromModelColumn(title: 'ID', type: 'number', editable: false)]
  protected int|null $id = null;
}