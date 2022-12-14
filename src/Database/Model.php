<?php

namespace PromCMS\Core\Database;

use BadMethodCallException;
use Error;
use SleekDB\Store as SleekStore;

abstract class Model
{
  use \PromCMS\Core\Database\Traits\Model\Events, \PromCMS\Core\Database\Traits\Model\Properties, \PromCMS\Core\Database\Traits\Model\Store;

  function __construct()
  {
    $this->bootIfNotBooted();
  }

  /**
   * The array of booted models.
   *
   * @var array
   */
  protected static $booted = [];

  /**
   * Boots model which creates store connection
   */
  public function bootIfNotBooted()
  {
    if (!isset(static::$booted[static::class])) {
      if (!$this->tableName) {
        $this->tableName = lcfirst(static::class);
      }

      $this->store = new SleekStore(
        $this->tableName,
        static::$databaseDirectory,
        static::$storeConfiguration,
      );
    }
  }

  /**
   * Creates a new database query
   */
  public function query(): Query
  {
    return new Query($this->getStore(), $this, static::$defaultLanguage);
  }

  /**
   * Handle dynamic method calls into the model.
   *
   * @param  string  $method
   * @param  array  $parameters
   * @return mixed
   */
  public function __call($method, $parameters)
  {
    if (method_exists(Query::class, $method)) {
      return $this->query()->{$method}(...$parameters);
    }

    try {
      return $this->{$method}(...$parameters);
    } catch (Error | BadMethodCallException $e) {
      if ($e instanceof BadMethodCallException) {
        throw new BadMethodCallException(
          sprintf('Call to undefined method %s::%s()', static::class, $method),
        );
      }

      throw $e;
    }
  }

  /**
   * 
   */
  public static function __callStatic($method, $arguments)
  {
    return (new static())->query()->{$method}(...$arguments);
  }
}
