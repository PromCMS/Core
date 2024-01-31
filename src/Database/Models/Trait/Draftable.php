<?php

namespace PromCMS\Core\Database\Models\Trait;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PromMapping;

trait Draftable
{
  #[ORM\Column(name: 'is_published', type: 'boolean')]
  #[PromMapping\PromModelColumn(title: 'Is published', type: 'boolean')]
  protected bool $published = false;

  public function publish()
  {
    if ($this->published === false) {
      $this->published = true;
    }

    return $this;
  }

  public function unpublish()
  {
    if ($this->published === true) {
      $this->published = false;
    }

    return $this;
  }
}