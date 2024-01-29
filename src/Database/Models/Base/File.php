<?php

/**
 * This file is generated by PromCMS, do not edit this file as changes made to this file will be overriden in the next model sync.
 * Updates should be made to ../File.php as that is not overriden.
 */

namespace PromCMS\Core\Database\Models\Base;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PROM;
use Doctrine\Common\Collections\ArrayCollection;
use PromCMS\Core\Database\Models\Abstract\Entity;

#[ORM\MappedSuperclass]
class File extends Entity
{
  use \PromCMS\Core\Database\Models\Trait\Timestamps;
  use \PromCMS\Core\Database\Models\Trait\Localized {
    getTranslations as protected getTranslationsOriginal;
  }
  use \PromCMS\Core\Database\Models\Trait\NumericId;
  
  #[ORM\Column(name: 'filename', nullable: false, unique: false, type: 'string'), PROM\PromModelColumn(title: 'Filename', type: 'string', editable: false, hide: false, localized: false)]
  protected ?string $filename;
  
  #[ORM\Column(name: 'mimetype', nullable: false, unique: false, type: 'string'), PROM\PromModelColumn(title: 'Mime type', type: 'string', editable: false, hide: false, localized: false)]
  protected ?string $mimeType;
  
  #[ORM\Column(name: 'filepath', nullable: false, unique: false, type: 'text'), PROM\PromModelColumn(title: 'Filepath', type: 'longText', editable: false, hide: false, localized: false)]
  protected ?string $filepath;
  
  #[ORM\Column(name: 'private', nullable: true, unique: false, type: 'boolean'), PROM\PromModelColumn(title: 'Private', type: 'boolean', editable: false, hide: false, localized: false)]
  protected ?bool $private;
  
  #[ORM\Column(name: 'description', nullable: true, unique: false, type: 'text'), PROM\PromModelColumn(title: 'Description', type: 'longText', editable: false, hide: false, localized: true)]
  protected ?string $description;
  /**
  * @var ArrayCollection<int, \PromCMS\Core\Database\Models\FileTranslation>
  */
  
  #[ORM\OneToMany(targetEntity: \PromCMS\Core\Database\Models\FileTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
  protected ?ArrayCollection $translations;
  
  function __construct()
  {
    $this->translations = new ArrayCollection();
  }
  
  #[ORM\PostLoad]
  function __prom__initCollections()
  {
    $this->translations ??= new ArrayCollection();
  }
  /**
  * @var ArrayCollection<string, \PromCMS\Core\Database\Models\FileTranslation>
  */
  
  function getTranslations(): ArrayCollection
  {
    return $this->getTranslationsOriginal();
  }
  
  function getFilename(): string
  {
    return $this->filename;
  }
  
  function setFilename(string $filename): static
  {
    $this->filename = $filename;
    return $this;
  }
  
  function getMimeType(): string
  {
    return $this->mimeType;
  }
  
  function setMimeType(string $mimeType): static
  {
    $this->mimeType = $mimeType;
    return $this;
  }
  
  function getFilepath(): string
  {
    return $this->filepath;
  }
  
  function setFilepath(string $filepath): static
  {
    $this->filepath = $filepath;
    return $this;
  }
  
  function getPrivate(): ?bool
  {
    return $this->private;
  }
  
  function setPrivate(?bool $private): static
  {
    $this->private = $private;
    return $this;
  }
  
  function getDescription(): ?string
  {
    return $this->description;
  }
  
  function setDescription(?string $description): static
  {
    $this->description = $description;
    return $this;
  }
}