<?php

namespace PromCMS\Core\Services;

use DI\Container;

class UserRoleService
{

  public function __construct(Container $container)
  {
  }

  private static $ADMIN_USER_ROLE = [
    'id' => 0,
    'label' => 'Admin',
    'slug' => 'admin',
    'description' => 'Main user role provided by PromCMS Core module',
  ];

  public static function getAdminRole()
  {
    return static::$ADMIN_USER_ROLE;
  }
}