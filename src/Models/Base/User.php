<?php
/**
 * This file is generated by PromCMS, do not edit this file as changes made to this file will be overriden in the next model sync. 
 * Updates should be made to ../User.php as that is not overriden.
 */

namespace PromCMS\Core\Models\Base;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as Mapping;
use PromCMS\Core\Models\Abstract\BaseModel;

#[ORM\Entity]
#[ORM\Table(name: 'prom__users')]
#[Mapping\PromModel(ignoreSeeding: false)]
class User extends BaseModel
{
  use \PromCMS\Core\Models\Trait\Timestamps;
      #[ORM\Column(
      type: 'string', 
      unique: true,
      name: 'email',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Email', 
      type: 'string',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $email;
    
      #[ORM\Column(
      type: 'text', 
      name: 'password',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Password', 
      type: 'longText',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $password;
    
      #[ORM\Column(
      type: 'string', 
      name: 'firstname',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'First name', 
      type: 'string',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private string $firstname;
    
      #[ORM\Column(
      type: 'string', 
      enumType: UserState::class,
      name: 'state',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'State', 
      type: 'enum',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private UserState $state = UserState::INVITED;
    
      #[ORM\Column(
      type: 'string', 
      name: 'avatar_id',
      nullable: true,
    )]
    #[Mapping\PromModelColumn(
      title: 'Avatar', 
      type: 'relationship',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private ?\PromCMS\Core\Models\File $avatar;
    
      #[ORM\Column(
      type: 'string', 
      name: 'role_id',
      nullable: true,
    )]
    #[Mapping\PromModelColumn(
      title: 'Role', 
      type: 'relationship',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    private ?\PromCMS\Core\Models\UserRole $role;
    
  
  public function __construct() {
          }
}
