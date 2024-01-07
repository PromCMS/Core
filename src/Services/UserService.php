<?php

namespace PromCMS\Core\Services;

use DI\Container;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Paginate;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use PromCMS\Core\Http\WhereQueryParam;
use PromCMS\Core\Models\User;


class UserService
{
  private EntityManager $em;

  public function __construct(Container $container)
  {
    $this->em = $container->get(EntityManager::class);
  }

  private function createQb()
  {
    return $this->em->createQueryBuilder();
  }

  private function getRepository()
  {
    return $this->em->getRepository(User::class);
  }

  public function findOneBy(Expr|WhereQueryParam|Comparison|Andx $where, array $select = []): User
  {
    $query = $this->createQb()->from(User::class, 'u')->select('u');

    if ($where instanceof WhereQueryParam) {
      $where->toQuery($query, 'u');
    } else {
      $query->where($where);
    }

    $results = $query->getQuery()->getResult();

    if (count($results) === 0) {
      throw new EntityNotFoundException();
    }

    return $results[0];
  }

  public function getOneBy(string $field, $fieldValue, array $select = []): User
  {
    return $this->findOneBy(
      new WhereQueryParam("$field.=.$fieldValue"),
      $select
    );
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
    $userQuery = $this->createQb()->from(User::class, 'u')->select('u');

    if (!empty($where)) {
      if ($where instanceof WhereQueryParam) {
        $where->toQuery($userQuery, 'u');
      } else {
        $userQuery->where($where);
      }
    }

    return Paginate::fromQuery($userQuery)->execute($page, $perPage);
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