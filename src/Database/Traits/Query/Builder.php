<?php

namespace PromCMS\Core\Database\Traits\Query;

trait Builder
{
  function where(array $arg)
  {
    if (count($arg)) {
      $this->getQueryBuilder()->where($arg);
    }

    return $this;
  }

  function orderBy(array $arg)
  {
    $this->getQueryBuilder()->orderBy($arg);

    return $this;
  }

  function join(\Closure $arg, string $propertyName)
  {
    $this->getQueryBuilder()->join($arg, $propertyName);

    return $this;
  }

  function limit($count)
  {
    $this->getQueryBuilder()->limit($count);

    return $this;
  }

  function skip($count)
  {
    $this->getQueryBuilder()->skip($count);

    return $this;
  }

  /**
   * Select what fields to include in result
   */
  function select(array $fieldNames)
  {
    $this->getQueryBuilder()->select($fieldNames);

    return $this;
  }

  /**
   * Opposite of select
   */
  function except(array $fieldNames)
  {
    $this->getQueryBuilder()->except($fieldNames);

    return $this;
  }
}
