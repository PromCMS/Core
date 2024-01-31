<?php

namespace PromCMS\Core\Database\Models\Trait;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PromMapping;

trait Timestamps
{
  #[ORM\Column(type: 'datetime', name: 'created_at', )]
  #[PromMapping\PromModelColumn(title: 'Created at', type: 'dateTime')]
  protected \DateTimeInterface $createdAt;

  #[ORM\Column(type: 'datetime', name: 'updated_at', nullable: true)]
  #[PromMapping\PromModelColumn(title: 'Updated at', type: 'dateTime')]
  protected ?\DateTimeInterface $updatedAt = null;

  #[ORM\PrePersist]
  #[ORM\PreUpdate]
  public function __prom_updatedTimestamps(): void
  {
    $this->updatedAt = new \DateTime('now');

    if (empty($this->createdAt)) {
      $this->createdAt = new \DateTime('now');
    }
  }
}