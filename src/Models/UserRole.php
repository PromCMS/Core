<?php

namespace PromCMS\Core\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Abstract\BaseModel;

#[ORM\Entity]
#[ORM\Table(name: 'prom__user_roles')]
#[Mapping\PromModel(adminMetadataIcon: 'UserExclamation')]
class UserRole extends BaseModel
{
  #[ORM\Column(type: 'string', unique: true)]
  #[Mapping\PromModelColumn(title: 'Label', type: 'string')]
  private string $label;

  #[ORM\Column(type: 'string', unique: true)]
  #[Mapping\PromModelColumn(title: 'Label', type: 'string')]
  private string $description;

  #[ORM\Column(type: 'array')]
  #[Mapping\PromModelColumn(title: 'Permissions', type: 'json')]
  private string $permissions;
}
