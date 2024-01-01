<?php
/**
 * This file is generated by PromCMS, do not edit this file as changes made to this file will be overriden in the next model sync. 
 * Updates should be made to ../File.php as that is not overriden.
 */

namespace PromCMS\Core\Models\Base;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as Mapping;
use PromCMS\Core\Models\Abstract\BaseModel;

#[ORM\Entity]
#[ORM\Table(name: 'prom__files')]
#[Mapping\PromModel(ignoreSeeding: true)]
class File extends BaseModel
{
  use \PromCMS\Core\Models\Trait\Timestamps;
      #[ORM\Column(
      type: 'string', 
      name: 'filename',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Filename', 
      type: 'string',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $filename;
    
      #[ORM\Column(
      type: 'string', 
      name: 'mimetype',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Mime type', 
      type: 'string',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $mimeType;
    
      #[ORM\Column(
      type: 'text', 
      name: 'filepath',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Filepath', 
      type: 'longText',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $filepath;
    
      #[ORM\Column(
      type: 'boolean', 
      name: 'private',
      nullable: true,
    )]
    #[Mapping\PromModelColumn(
      title: 'Private', 
      type: 'boolean',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private ?bool $private = false;
    
      #[ORM\Column(
      type: 'text', 
      name: 'description',
      nullable: true,
    )]
    #[Mapping\PromModelColumn(
      title: 'Description', 
      type: 'longText',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private ?string $description;
    
  
  public function __construct() {
          }
}
