<?php

namespace PromCMS\Core\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Abstract\BaseModel;
use PromCMS\Core\Password;

#[ORM\Entity]
#[ORM\Table(name: 'prom__users')]
#[Mapping\PromModel(adminMetadataIcon: 'Users')]
class User extends BaseModel
{
  #[ORM\Column(type: 'string', unique: true, updatable: false)]
  #[Mapping\PromModelColumn(title: 'Email', type: 'string')]
  private string $email;

  #[ORM\Column(type: 'text')]
  #[Mapping\PromModelColumn(title: 'Password', type: 'password', editable: false, adminMetadataIsHidden: true, hide: true)]
  private string $password;

  #[ORM\Column(type: 'string')]
  #[Mapping\PromModelColumn(title: 'First name', type: 'string')]
  private string $firstname;

  #[ORM\Column(type: 'string')]
  #[Mapping\PromModelColumn(title: 'Last name', type: 'string')]
  private string $lastname;

  #[ORM\Column(type: 'string', enumType: UserState::class)]
  #[Mapping\PromModelColumn(title: 'State', type: 'enum')]
  private UserState $state = UserState::INVITED;

  #[ORM\ManyToOne(targetEntity: File::class)]
  #[ORM\JoinColumn(name: 'avatar_id', referencedColumnName: 'id', nullable: true)]
  #[Mapping\PromModelColumn(title: 'Avatar', type: 'file')]
  private File|null $avatar = null;

  #[ORM\ManyToOne(targetEntity: UserRole::class)]
  #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
  #[Mapping\PromModelColumn(title: 'Role', type: 'relationship')]
  private UserRole|null $role = null;

  // static function getPrivateFields(): array
  // {
  //   return array_merge(static::$privateFields, array_map(fn($item) => ucfirst($item), static::$privateFields));
  // }

  public function getId(): int|null
  {
    return $this->id;
  }

  public function getName(): string
  {
    return $this->firstname . " " . $this->lastname;
  }

  public function setName(string $name)
  {
    [$firstname, $lastname] = explode(' ', $name);

    if (empty($firstname) || empty($lastname)) {
      throw new \Exception("Cannot set user name with just one part of name. Name must be in format '<first-name> <last-name>'");
    }

    $this->firstname = $firstname;
    $this->lastname = $lastname;

    return $this;
  }

  public function isBlocked(): bool
  {
    return $this->state === UserState::BLOCKED;
  }

  public function checkPassword(string $checkAgainst)
  {
    $userPassword = $this->password;

    if (!$userPassword) {
      throw new \Exception('Cannot check password because user does not have any password');
    }

    return Password::check($checkAgainst, $userPassword);
  }
}
