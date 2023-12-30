<?php

namespace PromCMS\Core\Models\Trait;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as PromMapping;

trait Draftable
{
  #[ORM\Column(name: 'is_published', type: 'boolean')]
  #[PromMapping\PromModelColumn(title: 'Is published', type: 'boolean')]
  private bool $published = false;

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