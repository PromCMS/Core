<?php
/**
 * This file is generated by PromCMS, do not edit this file as changes made to this file will be overriden in the next model sync. 
 * Updates should be made to ../User.php as that is not overriden.
 */

namespace PromCMS\Core\Models\Base;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as Mapping;
use PromCMS\Core\Models\Abstract\BaseModel;

abstract class User extends BaseModel
{
use \PromCMS\Core\Models\Trait\Timestamps;
use \PromCMS\Core\Models\Trait\NumericId;

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
    protected string $email;
    
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
    protected string $password;
    
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
    protected string $firstname;
    
      #[ORM\Column(
      type: 'string', 
      name: 'lastname',
      nullable: false,
    )]
    #[Mapping\PromModelColumn(
      title: 'Last name', 
      type: 'string',
      editable: 'true',
      hide: 'false',
      localized: 'false'
    )]
    protected string $lastname;
    
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
    protected UserState $state = UserState::INVITED;
    
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
    protected ?\PromCMS\Core\Models\File $avatar;
    
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
    protected ?\PromCMS\Core\Models\UserRole $role;
    
  
  public function __construct() {
          }

    public function getEmail() {
    return $this->email;
  }
  
  public function setEmail(string $email) {
    return $this->email = $email;
  }
  public function getPassword() {
    return $this->password;
  }
  
  public function setPassword(string $password) {
    return $this->password = $password;
  }
  public function getFirstname() {
    return $this->firstname;
  }
  
  public function setFirstname(string $firstname) {
    return $this->firstname = $firstname;
  }
  public function getLastname() {
    return $this->lastname;
  }
  
  public function setLastname(string $lastname) {
    return $this->lastname = $lastname;
  }
  public function getState() {
    return $this->state;
  }
  
  public function setState(UserState $state) {
    return $this->state = $state;
  }
  public function getAvatar() {
    return $this->avatar;
  }
  
  public function setAvatar(\PromCMS\Core\Models\File|null $avatar) {
    return $this->avatar = $avatar;
  }
  public function getRole() {
    return $this->role;
  }
  
  public function setRole(\PromCMS\Core\Models\UserRole|null $role) {
    return $this->role = $role;
  }
  }
