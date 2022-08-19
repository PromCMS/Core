<?php
namespace PromCMS\Core\Database\Traits\Query;

use SleekDB\QueryBuilder;
use SleekDB\Store;

trait Managers
{
  protected QueryBuilder $queryBuilder;

  /**
   * Gets a sleekdb QueryBuilder instance
   */
  public function getQueryBuilder(): QueryBuilder
  {
    if (!isset($this->queryBuilder)) {
      $this->queryBuilder = $this->getStore()
        ->createQueryBuilder()
        ->select($this->getFieldKeyAliases());
    }

    return $this->queryBuilder;
  }

  /**
   * Destroy query builder that was previously created and used
   */
  public function destroyQueryBuilder()
  {
    unset($this->queryBuilder);
  }

  private function getStore(): Store
  {
    return $this->store;
  }
}

