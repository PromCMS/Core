<?php

namespace PromCMS\Core\Database;

abstract class SingletonModel extends Model
{
  /**
   * The name of singleton model
   */
  protected string $name;

  // The table name is static
  protected string $tableName = "_singletons";

  /**
   * Getter of singleton name
   */
  public function getName(): string
  {
    return $this->name;
  }

  function __construct()
  {
    // Ensure name which can be set or not set when extending this class
    if (!isset($this->name)) {
      $this->name = lcfirst(static::class);
    }

    parent::__construct();

    $this->bootIfNotBooted();
  }
}
