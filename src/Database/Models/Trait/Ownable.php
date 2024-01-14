<?php

namespace PromCMS\Core\Database\Models\Trait;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\User;
use PromCMS\Core\Database\Models\Mapping as PromMapping;

trait Ownable
{
  #[ORM\ManyToOne(targetEntity: User::class)]
  #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  #[PromMapping\PromModelColumn(title: 'Created by', type: 'relationship')]
  protected User|null $createdBy = null;

  #[ORM\ManyToOne(targetEntity: User::class)]
  #[ORM\JoinColumn(name: 'updated_by_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  #[PromMapping\PromModelColumn(title: 'Updated by', type: 'relationship')]
  protected User|null $updateBy = null;

  public function setUpdatedBy(User $user)
  {
    $this->updateBy = $user;
  }

  public function setCreatedBy(User $user)
  {
    $this->createdBy = $user;
  }
}