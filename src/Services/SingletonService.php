<?php

namespace PromCMS\Core\Services;

use PromCMS\Core\Database\ModelResult;
use PromCMS\Core\Database\Query;
use PromCMS\Core\Database\SingletonModel;
use PromCMS\Core\Exceptions\EntityNotFoundException;

class SingletonService
{
  protected SingletonModel $modelInstance;
  protected string $language;

  public function __construct(SingletonModel $modelInstance, $language = null)
  {
    $this->modelInstance = $modelInstance;
    if ($language) {
      $this->language = $language;
    }
  }

  private function connectWhereQuery($where = [])
  {
    return array_merge($where, [Query::$SINGLETON_NAME_FIELD_NAME, '=', $this->modelInstance->getName()]);
  }

  /**
   * Get singleton data
   */
  public function getOne($where): ModelResult
  {
    $query = $this->modelInstance->query();

    if (isset($this->language)) {
      $query->setLanguage($this->language);
    }

    try {
      return $query->where($this->connectWhereQuery($where))->getOne();
    } catch (\Exception $ex) {
      // If item does not exist we just create it with out anything
      if ($ex instanceof EntityNotFoundException) {
        return $query->create([
          Query::$SINGLETON_NAME_FIELD_NAME => $this->modelInstance->getName()
        ]);
      }

      throw $ex;
    }
  }

  /**
   * Update singleton data
   */
  public function update(array $where, array $payload): ModelResult
  {
    $query = $this->modelInstance->query();

    // Just get rid of singleton field name
    if (isset($payload[Query::$SINGLETON_NAME_FIELD_NAME])) {
      unset($payload[Query::$SINGLETON_NAME_FIELD_NAME]);
    }

    if (isset($this->language)) {
      $query->setLanguage($this->language);
    }

    try {
      return $query->where($this->connectWhereQuery($where))->update($payload);
    } catch (\Exception $ex) {
      // If item does not exist we just create it with update payload
      if ($ex instanceof EntityNotFoundException) {
        $payload[Query::$SINGLETON_NAME_FIELD_NAME] = $this->modelInstance->getName();

        return $query->create($payload);
      }

      throw $ex;
    }
  }

  /**
   * Clear singleton data
   */
  public function clear(array $where): ModelResult
  {
    $item = $this->modelInstance->where($this->connectWhereQuery($where))->delete();

    return $this->getOne([]);
  }
}
