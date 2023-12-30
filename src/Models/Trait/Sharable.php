<?php

namespace PromCMS\Core\Models\Trait;

use PromCMS\Core\Models\Mapping as PromMapping;
use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\User;

trait Sharable
{
  #[ORM\Column(type: 'array', nullable: true)]
  #[PromMapping\PromModelColumn(title: 'Coeditors', type: 'json')]
  private array|null $coeditors = null;

  public function shareWith(User $user)
  {
    $this->coeditors = $this->coeditors ?? [];

    // TODO - check target user if user has sufficient permissions to atleast update

    $this->coeditors[$user->getId()] = true;
  }
}