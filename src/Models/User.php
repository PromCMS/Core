<?php

namespace PromCMS\Core\Models;

use PromCMS\Core\Models\Base\UserState;
use PromCMS\Core\Password;
use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Models\Mapping as PromMapping;

#[ORM\Entity, ORM\Table(name: 'prom__users'), PromMapping\PromModel(ignoreSeeding: false), ORM\HasLifecycleCallbacks]
class User extends Base\User
{
  // static function getPrivateFields(): array
  // {
  //   return array_merge(static::$privateFields, array_map(fn($item) => ucfirst($item), static::$privateFields));
  // }

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

  public function isAdmin(): bool
  {
    return $this->getRoleSlug() === 'admin';
  }
}
