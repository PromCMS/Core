<?php

namespace PromCMS\Core\Models\Trait;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\User;
use PromCMS\Core\Models\Mapping as PromMapping;

trait Ownable
{
  #[ORM\ManyToOne(targetEntity: User::class)]
  #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  #[PromMapping\PromModelColumn(title: 'Created by', type: 'relationship')]
  private User|null $createdBy = null;

  #[ORM\ManyToOne(targetEntity: User::class)]
  #[ORM\JoinColumn(name: 'owned_by_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  #[PromMapping\PromModelColumn(title: 'Owned by', type: 'relationship')]
  private User|null $ownedBy = null;
}