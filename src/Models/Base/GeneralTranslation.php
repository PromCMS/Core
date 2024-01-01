<?php
/**
 * This file is generated by PromCMS, do not edit this file as changes made to this file will be overriden in the next model sync. 
 * Updates should be made to ../GeneralTranslation.php as that is not overriden.
 */

namespace PromCMS\Core\Models\Base;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as Mapping;
use PromCMS\Core\Models\Abstract\BaseModel;

#[ORM\Entity]
#[ORM\Table(name: 'prom__general_translations')]
#[Mapping\PromModel(ignoreSeeding: true)]
class GeneralTranslation extends BaseModel
{
use \PromCMS\Core\Models\Trait\Timestamps;
use \PromCMS\Core\Models\Trait\NumericId;

      #[ORM\Column(
      type: 'string', 
      unique: true,
      name: 'lang',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Language', 
      type: 'string',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $lang;
    
      #[ORM\Column(
      type: 'string', 
      unique: true,
      name: 'key',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Key', 
      type: 'string',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $key;
    
      #[ORM\Column(
      type: 'string', 
      name: 'value',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Value', 
      type: 'string',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $value;
    
  
  public function __construct() {
          }
}
