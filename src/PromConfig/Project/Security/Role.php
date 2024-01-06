<?php

namespace PromCMS\Core\PromConfig\Project\Security;

class Role
{
  private static $defaultPermissisonsSet = [
    RolePermissionOptionKey::CREATE => RolePermissionOptionValue::DENY,
    RolePermissionOptionKey::READ => RolePermissionOptionValue::DENY,
    RolePermissionOptionKey::UPDATE => RolePermissionOptionValue::DENY,
    RolePermissionOptionKey::DELETE => RolePermissionOptionValue::DENY,
  ];

  public function __construct(
    public readonly string $name,
    public readonly string $slug,
    private array|string $modelPermissions,
    public readonly ?bool $hasAccessToAdmin = true,
  ) {
  }

  /**
   * @return array<RolePermissionOptionKey, RolePermissionOptionValue>
   */
  public function getPermissionSetForModel(string $tableName): array
  {
    if (is_string($this->modelPermissions)) {
      $permission = RolePermissionOptionValue::tryFrom($this->modelPermissions) ?? RolePermissionOptionValue::DENY;

      return array_fill_keys(array_keys(static::$defaultPermissisonsSet), $permission);
    }

    if (empty($permissionSetFromConfig = $this->modelPermissions[$tableName])) {
      return static::$defaultPermissisonsSet;
    }

    if (is_string($permissionSetFromConfig)) {
      return array_fill_keys(array_keys(static::$defaultPermissisonsSet), $permissionSetFromConfig);
    }

    return array_merge(static::$defaultPermissisonsSet, $permissionSetFromConfig);
  }

  public function __toArray()
  {
    return [
      'name' => $this->name,
      'slug' => $this->slug,
      'hasAccessToAdmin' => $this->hasAccessToAdmin,
      'modelPermissions' => array_map(fn($key) => $this->getPermissionSetForModel($key), array_keys($this->modelPermissions)),
    ];
  }
}