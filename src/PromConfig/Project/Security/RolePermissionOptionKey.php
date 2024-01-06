<?php

namespace PromCMS\Core\PromConfig\Project\Security;

enum RolePermissionOptionKey: string
{
  case CREATE = 'c';
  case READ = 'r';
  case UPDATE = 'u';
  case DELETE = 'd';
}
