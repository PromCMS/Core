<?php

namespace PromCMS\Core\Models\Trait;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as PromMapping;

trait NumericId
{
  #[ORM\Id]
  #[ORM\Column(type: 'integer')]
  #[ORM\GeneratedValue]
  #[PromMapping\PromModelColumn(title: 'ID', type: 'number', editable: false)]
  private int|null $id = null;
}