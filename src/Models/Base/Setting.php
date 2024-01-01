<?php
/**
 * This file is generated by PromCMS, do not edit this file as changes made to this file will be overriden in the next model sync. 
 * Updates should be made to ../Setting.php as that is not overriden.
 */

namespace PromCMS\Core\Models\Base;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as Mapping;
use PromCMS\Core\Models\Abstract\BaseModel;

#[ORM\Entity]
#[ORM\Table(name: 'prom__settings')]
#[Mapping\PromModel(ignoreSeeding: true)]
class Setting extends BaseModel
{
use \PromCMS\Core\Models\Trait\Timestamps;
use \PromCMS\Core\Models\Trait\Ownable;
use \PromCMS\Core\Models\Trait\NumericId;

      #[ORM\Column(
      type: 'string', 
      unique: true,
      name: 'name',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Name', 
      type: 'string',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $name;
    
      #[ORM\Column(
      type: 'array', 
      name: 'content',
      nullable: true,
    )]
    #[Mapping\PromModelColumn(
      title: 'Content', 
      type: 'json',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private ?array $content = [];
    
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
