<?php

namespace PromCMS\Core\Utils;

class ModelUtils
{

  /**
   * This generates mysql search params
   */
  static function getOnlyOwnersOrEditorsFilter(int $ownerId, $classInstance)
  {
    return !$classInstance->getSummary()->isSharable
      ? ['created_by', '=', $ownerId]
      : [
        ['created_by', '=', $ownerId],
        'OR',
        ["coeditors.$ownerId", '=', true],
      ];
  }
}