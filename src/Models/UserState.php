<?php

namespace PromCMS\Core\Models;

final class UserState
{
  public static string $ACTIVE = "active";
  public static string $INVITED = "invited";
  public static string $BLOCKED = "blocked";
  public static string $PASSWORD_RESET = "password-reset";
}