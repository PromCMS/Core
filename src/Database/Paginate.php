<?php

namespace PromCMS\Core\Database;

use Doctrine\ORM\Tools\Pagination\Paginator;

class Paginate
{
  private int $total;
  private int $lastPage;
  private int $currentPage;
  private $items;

  public function __construct(private \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder $query)
  {
  }

  /**
   * @param int $page
   * @param int $limit
   * @return Paginate
   */
  public function execute(int $page = 1, int $limit = 15): Paginate
  {
    $paginator = new Paginator($this->query);

    $paginator
      ->getQuery()
      ->setFirstResult($limit * ($page - 1))
      ->setMaxResults($limit);

    $this->currentPage = $page;
    $this->total = $paginator->count();
    $this->lastPage = (int) ceil($paginator->count() / $paginator->getQuery()->getMaxResults());
    $this->items = $paginator;

    return $this;
  }

  public function getTotal(): int
  {
    return $this->total;
  }

  public function getLastPage(): int
  {
    return $this->lastPage;
  }

  public function getCurrentPage(): int
  {
    return $this->currentPage;
  }

  public function getItems()
  {
    return $this->items;
  }
}