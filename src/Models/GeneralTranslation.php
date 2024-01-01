<?php

namespace PromCMS\Core\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as Mapping;
use PromCMS\Core\Models\Abstract\BaseModel;

#[ORM\Entity]
#[ORM\Table(name: 'prom__general_translations')]
#[Mapping\PromModel()]
class GeneralTranslation extends BaseModel
{
  #[ORM\Column(type: 'string', length: 20)]
  #[Mapping\PromModelColumn(title: 'Language', type: 'string')]
  private string $lang;

  #[ORM\Column(type: 'string')]
  #[Mapping\PromModelColumn(title: 'Key', type: 'string')]
  private string $key;

  #[ORM\Column(type: 'array')]
  #[Mapping\PromModelColumn(title: 'Value', type: 'string')]
  private string $value;
}
