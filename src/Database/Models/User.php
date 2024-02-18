<?php

/**
 * This file is generated by PromCMS, however you can add methods and other logic to this class
 * as this file will be just checked for presence of class in next models sync.
 */
namespace PromCMS\Core\Database\Models;

use Doctrine\ORM\Mapping as ORM;
use PromCMS\Core\Database\Models\Mapping as PROM;
use PromCMS\Core\Database\Models\Base\UserState;
use PromCMS\Core\Password;

#[ORM\Entity, ORM\Table(name: 'prom__users'), PROM\PromModel(ignoreSeeding: true), ORM\HasLifecycleCallbacks]
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
    return $this->getRole() === 'admin';
  }

  public function setPassword(string $password): static
  {
    $this->password = Password::hash($password);
    return $this;
  }

  public function fill(array $values)
  {
    parent::fill($values);
    if (isset($values['name'])) {
      $this->setName($values['name']);
    }
    if (isset($values['password']) && !empty($newPassword = $values['password'])) {
      $this->setPassword($newPassword);
    }
  }

  public function toArray()
  {
    $result = parent::toArray();
    $result['name'] = $this->getName();
    return $result;
  }
}