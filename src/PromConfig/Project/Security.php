<?php

namespace PromCMS\Core\PromConfig\Project;

use PromCMS\Core\PromConfig\Project\Security\Roles;

class Security
{
  public function __construct(
    /**
     * @var Roles
     */
    public readonly Roles $roles
  ) {
  }
}