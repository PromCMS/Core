<?php

namespace PromCMS\Core\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Abstract\BaseModel;
use PromCMS\Core\Models\Trait\Ownable;
use PromCMS\Core\Models\Trait\Timestamps;

#[ORM\Entity]
#[ORM\Table(name: 'prom__settings', indexes: [['name' => 'settings_search_name', 'columns' => ['name']]])]
#[Mapping\PromModel(ignoreSeeding: true)]
class Setting extends BaseModel
{
  use Ownable;
  use Timestamps;

  #[ORM\Column(type: 'string', unique: true)]
  #[Mapping\PromModelColumn(title: 'Name', type: 'string')]
  private string $name;

  #[ORM\Column(type: 'text', nullable: true)]
  #[Mapping\PromModelColumn(title: 'Description', type: 'longText')]
  private string $description;

  #[ORM\Column(type: 'array', nullable: true)]
  #[Mapping\PromModelColumn(title: 'Content', type: 'json')]
  private string $content;
}
