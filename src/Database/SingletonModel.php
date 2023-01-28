<?php

namespace PromCMS\Core\Database;

abstract class SingletonModel extends Model
{
  /**
   * The name of singleton model
   */
  protected string $name;

  /**
   * Getter of singleton name
   */
  public function getName(): string
  {
    return $this->name;
  }

  function __construct()
  {
    // The table name is static
    $this->tableName = "_singletons";
    if (!isset($this->name)) {
      $this->name = lcfirst(static::class);
    }

    parent::__construct();

    $this->bootIfNotBooted();
  }
}
