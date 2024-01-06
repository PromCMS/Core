<?php
namespace PromCMS\Core\Internal\Config;

class ConfigBase
{
  function __construct(
  #[\SensitiveParameter]
    array $config
  ) {
    $keys = array_keys(get_class_vars(static::class));

    foreach ($keys as $key) {
      if (!empty($config[$key])) {
        $this->{$key} = $config[$key];
      }
    }
  }

  /**
   * Converts properties to array recursively
   */
  function __toArray(): array
  {
    $properties = get_object_vars($this);

    foreach ($properties as $propertyName => $propertyValue) {
      if ($propertyValue instanceof ConfigBase) {
        $properties[$propertyName] = $propertyValue->__toArray();
      }
    }

    return $properties;
  }
}