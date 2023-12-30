<?php

namespace PromCMS\Core\Models;

enum UserState: string
{
  case ACTIVE = "active";
  case INVITED = "invited";
  case BLOCKED = "blocked";
  case PASSWORD_RESET = "password-reset";
}