<?php

namespace PromCMS\Core;

/**
 * @deprec Use Symfony Path instead 
 */
class Path
{

  /**
   * Joins path by systems directory separator
   */
  public static function join(...$inp)
  {
    return implode(DIRECTORY_SEPARATOR, $inp);
  }
}
