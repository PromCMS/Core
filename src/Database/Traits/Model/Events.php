<?php

namespace PromCMS\Core\Database\Traits\Model;

use PromCMS\Core\Database\ModelResult;

trait Events
{
  public static function beforeSafe(array $args): array
  {
    return $args;
  }

  public static function beforeCreate(array $args): array
  {
    return $args;
  }

  public static function afterCreate(ModelResult $item): ModelResult
  {
    return $item;
  }
}
