<?php

namespace PromCMS\Core\Models;

use PromCMS\Core\Models\Base\User as BaseUser;
use PromCMS\Core\Models\Map\UserTableMap;
use PromCMS\Core\Password;
use Propel\Runtime\Map\TableMap;

/**
 * Skeleton subclass for representing a row from the 'prom__users' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class User extends BaseUser
{
  private static $privateFields = ["password"];

  static function getPrivateFields(): array
  {
    return array_merge(static::$privateFields, array_map(fn($item) => ucfirst($item), static::$privateFields));
  }

  public function getName(): string
  {
    return $this->getFirstname() . " " . $this->getLastname();
  }

  public function setName(string $name)
  {
    [$firstname, $lastname] = explode(' ', $name);

    if (empty($firstname) || empty($lastname)) {
      throw new \Exception("Cannot set user name with just one part of name. Name must be in format '<first-name> <last-name>'");
    }

    $this->setFirstname($firstname);
    $this->setLastname($lastname);

    return $this;
  }

  public function isBlocked(): bool
  {
    return $this->state === UserState::$BLOCKED;
  }

  public function fromArray(array $arr, string $keyType = TableMap::TYPE_PHPNAME): User
  {
    $userTableMap = new UserTableMap();
    $stateFieldKey = UserTableMap::translateFieldName("state", TableMap::TYPE_CAMELNAME, $keyType);

    // We have to format this manually, enums from propel are just tiny ints
    if (isset($arr[$stateFieldKey]) && is_int($arr[$stateFieldKey])) {
      $stateColumnEnumValues = $userTableMap->getColumn('state')->getValueSet();

      $arr[$stateFieldKey] = $stateColumnEnumValues[$arr[$stateFieldKey]];
    }

    return parent::fromArray($arr, $keyType);
  }


  public function toArray(string $keyType = TableMap::TYPE_CAMELNAME, bool $includeLazyLoadColumns = true, array $alreadyDumpedObjects = [], bool $includeForeignObjects = false): array
  {
    $result = parent::toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, $includeForeignObjects);

    $privateFields = static::getPrivateFields();
    foreach ($privateFields as $privateField) {
      if (isset($result[$privateField])) {
        unset($result[$privateField]);
      }
    }

    return $result;
  }

  public function checkPassword(string $checkAgainst)
  {
    $userPassword = $this->getPassword();

    if (!$userPassword) {
      throw new \Exception('Cannot check password because user does not have any password');
    }

    return Password::check($checkAgainst, $userPassword);
  }
}
