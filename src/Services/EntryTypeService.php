<?php

namespace PromCMS\Core\Services;

use PromCMS\Core\Database\Model;
use PromCMS\Core\Database\ModelResult;

class EntryTypeService
{
  protected Model $modelInstance;
  protected string $language;

  public function __construct(Model $modelInstance, $language = null)
  {
    $this->modelInstance = $modelInstance;
    if ($language) {
      $this->language = $language;
    }
  }

  /**
   * Create item
   */
  public function create(array $payload): ModelResult
  {
    return $this->modelInstance->create($payload);
  }

  /**
   * Get one
   */
  public function getOne($where): ModelResult
  {
    $query = $this->modelInstance->query();

    if (isset($this->language)) {
      $query->setLanguage($this->language);
    }

    return $query->where($where)->getOne();
  }

  /**
   * Get many items from current model
   */
  public function getMany(array $where = [], $page = 1, $pageLimit = 15): array
  {
    $query = $this->modelInstance->query();
    $page = intval($page) > 0 ? intval($page) : 1;

    if (isset($this->language)) {
      $query->setLanguage($this->language);
    }

    $query
      ->orderBy(
        $this->modelInstance->getSummary()->hasOrdering
          ? ['order' => 'asc', 'id' => 'asc']
          : ['id' => 'asc'],
      )
      ->limit($pageLimit)
      ->skip($pageLimit * ($page - 1));

    $total = count($this->modelInstance->where($where)->getMany());
    $query->where($where);
    $data = $query->getMany();
    $from = $pageLimit * ($page - 1) + 1;
    $to = $from + ($pageLimit - 1) - ($pageLimit - count($data));
    $lastPage = ceil($total / $pageLimit);

    return [
      'data' => $data,
      'current_page' => $page,
      'last_page' => $lastPage,
      'per_page' => $pageLimit,
      'total' => $total,
      'from' => $from,
      'to' => $to,
    ];
  }

  /**
   * Update an item
   */
  public function update(array $where, array $payload): ModelResult
  {
    $query = $this->modelInstance->query();

    if (isset($this->language)) {
      $query->setLanguage($this->language);
    }

    return $query->where($where)->update($payload);
  }

  /**
   * Delete an item
   */
  public function delete(array $where): ModelResult
  {
    $item = $this->modelInstance->where($where)->delete();

    return $item;
  }
}
