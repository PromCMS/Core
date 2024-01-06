<?php

namespace PromCMS\Core\Services;

use DI\Container;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Paginate;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use PromCMS\Core\Http\WhereQueryParam;
use PromCMS\Core\Models\User;


class UserService
{
  private EntityManager $em;
  private QueryBuilder $qb;

  public function __construct(Container $container)
  {
    $this->em = $container->get(EntityManager::class);
    $this->qb = $this->em->createQueryBuilder();
  }

  private function getRepository()
  {
    return $this->em->getRepository(User::class);
  }

  public function findOneBy(Expr|WhereQueryParam|Comparison|Andx $where, array $select = []): User
  {
    $query = $this->qb->from(User::class, 'u');

    if ($where instanceof WhereQueryParam) {
      $where->toQuery($query, 'u');
    } else {
      $query->where($where);
    }

    $result = $query->getQuery()->getResult();

    if (!$result) {
      throw new EntityNotFoundException();
    }

    return $result;
  }

  public function getOneBy(string $field, $fieldValue, array $select = []): User
  {
    return $this->findOneBy($this->qb->expr()->eq("u.$field", $fieldValue), $select);
  }

  public function getOneById($id, array $select = [])
  {
    return $this->getOneBy("id", $id, $select);
  }

  public function updateById(string|int $id, array $payload = []): User
  {
    $user = $this->getOneById($id);

    $user->fill($payload);
    $this->em->flush();

    return $user;
  }

  public function getManyPaged(
    $page,
    $perPage = 15,
    Expr|WhereQueryParam|Comparison|Andx|null $where = null,
    /**
     * @deprec
     */
    array $select = []
  ) {
    $userQuery = $this->qb->from(User::class, 'u');

    if (!empty($where)) {
      if ($where instanceof WhereQueryParam) {
        $where->toQuery($userQuery, 'u');
      } else {
        $userQuery->where($where);
      }
    }

    return new Paginate($userQuery);
  }

  public function create(array $payload): User
  {
    $create = new User();
    $create->fill($payload);

    $this->em->persist($create);
    $this->em->flush();

    // TODO: implement ensuring duplicates

    return $create;
  }

  public function deleteBy(Expr|WhereQueryParam|Comparison|Andx $where): void
  {
    $foundUser = $this->findOneBy($where);

    if (!$foundUser) {
      throw new EntityNotFoundException();
    }

    $this->em->remove($foundUser);
    $this->em->flush();
  }
}