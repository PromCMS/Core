<?php

namespace PromCMS\Core\PromConfig\Project\Security;

enum RolePermissionOptionValue: string
{
  case ALLOW_ALL = 'allow-all';
  case ALLOW_OWN = 'allow-own';
  case DENY = 'deny';
}