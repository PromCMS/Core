<?php

namespace PromCMS\Core\Models\Trait;

use Exception;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Models\Mapping as PromMapping;
use Doctrine\ORM\Mapping as ORM;

trait Ordable
{
  #[ORM\Column(type: 'integer', nullable: true)]
  #[PromMapping\PromModelColumn(title: 'Order', type: 'number', editable: false)]
  protected int|null $order = null;

  public function getOrder(): ?int
  {
    return $this->order;
  }

  /**
   * Set the value of [order] column.
   *
   * @param int|null $v New value
   */
  public function setOrder(int $v)
  {
    if ($this->order !== $v) {
      $this->order = $v;
    }

    return $this;
  }

  public function swapWith($object, EntityManager $em)
  {
    $em->getConnection()->beginTransaction();

    try {
      $oldRank = $this->getOrder();
      $newRank = $object->getOrder();

      $this->setOrder($newRank);
      $object->setOrder($oldRank);

      $em->persist($this);
      $em->persist($object);

      $em->flush();
      $em->getConnection()->commit();
    } catch (Exception $e) {
      $em->getConnection()->rollBack();
      throw $e;
    }

    return $this;
  }
}