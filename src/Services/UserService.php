<?php

namespace PromCMS\Core\Services;

use DI\Container;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use PromCMS\Core\Models\Map\UserTableMap;
use PromCMS\Core\Models\UserQuery;
use PromCMS\Core\Models\User;
use Propel\Runtime\Formatter\ObjectFormatter;
use Propel\Runtime\Map\TableMap;


class UserService
{

  public function __construct(Container $container)
  {
  }

  public function findOneBy(array $where, array $select = []): User
  {
    $userQuery = UserQuery::create();

    if (count($select)) {
      $userQuery->select($select);
    }

    $result = $userQuery->filterByArray($where)->findOne();

    if (!$result) {
      throw new EntityNotFoundException();
    }

    // This usually is true since we use select and that is formatted to array in propel
    if (is_array($result)) {
      $result = (new User())->hydrate($result, 0, false, TableMap::TYPE_PHPNAME);
    }

    return $result;
  }

  public function getOneBy(string $field, $fieldValue, array $select = []): User
  {
    return $this->findOneBy([
      ucfirst($field) => $fieldValue
    ], $select);
  }

  public function getOneById($id, array $select = [])
  {
    return $this->getOneBy("Id", $id);
  }

  public function updateById(string|int $id, array $payload = []): User
  {
    $user = $this->getOneById($id);

    $user->fromArray($payload);
    $user->save();

    return $user;
  }

  public function getManyPaged($page, $perPage = 15, array $where = [], array $select = [])
  {
    $userQuery = UserQuery::create();

    if (count($where) > 0) {
      $userQuery->filterByArray($where);
    }

    if (count($select) === 0) {
      $select = UserTableMap::getFieldNames();
    }

    $select = array_filter($select, fn($fieldName) => strtolower($fieldName) !== "password");

    // TODO: this kind of breaks formatting because with select the actual results uses simplearrayformatter which works in  a wierd way (adds "" around every field name)
    // $userQuery->select($select);

    $userQuery->setFormatter(ObjectFormatter::class);

    return $userQuery->paginate($page, $perPage);
  }

  public function create(array $payload): User
  {
    $create = new User();

    $create->fromArray($payload);
    $create->save();

    // TODO: implement ensuring duplicates

    return $create;
  }

  public function deleteBy(array $where): void
  {
    $userQuery = UserQuery::create();

    $deletedUsers = $userQuery->filterByArray($where)->delete();

    if ($deletedUsers === 0) {
      throw new EntityNotFoundException();
    }
  }
}