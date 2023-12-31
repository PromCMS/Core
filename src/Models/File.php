<?php

namespace PromCMS\Core\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Abstract\BaseModel;
use PromCMS\Core\Models\Trait\Ownable;
use PromCMS\Core\Models\Trait\Timestamps;

#[ORM\Entity]
#[ORM\Table(name: 'prom__files')]
#[Mapping\PromModel(ignoreSeeding: true)]
class File extends BaseModel
{
  use Ownable;
  use Timestamps;

  #[ORM\Column(type: 'string')]
  #[Mapping\PromModelColumn(title: 'Filename', type: 'string')]
  private string $filename;

  #[ORM\Column(type: 'string', name: 'mime_type')]
  #[Mapping\PromModelColumn(title: 'Mime type', type: 'string')]
  private string $mimeType;

  #[ORM\Column(type: 'text', unique: true)]
  #[Mapping\PromModelColumn(title: 'Filepath', type: 'string')]
  private string $filepath;

  #[ORM\Column(type: 'boolean', nullable: true)]
  #[Mapping\PromModelColumn(title: 'Private', type: 'boolean')]
  private string $private = false;

  #[ORM\Column(type: 'text', nullable: true)]
  #[Mapping\PromModelColumn(title: 'Description', type: 'longText')]
  private string|null $description = null;
}
