<?php

namespace PromCMS\Core\PromConfig\Project\Security;

class Roles
{
  /**
   * @var array<string, Role>|null
   */
  private array|null $roles;
  private array $rolesAsArray;

  public function __construct(array $roles = [])
  {
    $this->rolesAsArray = $roles;
  }

  /**
   * @return array<string, Role> 
   */
  public function getRoles()
  {
    if (isset($this->roles)) {
      return $this->roles;
    }

    $this->roles = [];

    foreach ($this->rolesAsArray as $roleArray) {
      $this->roles[$roleArray['slug']] = new Role(...$roleArray);
    }

    return $this->roles;
  }

  public function getRoleBySlug(string $slug): Role|null
  {
    return $this->getRoles()[$slug] ?? null;
  }
}