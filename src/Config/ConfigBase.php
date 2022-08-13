<?php
namespace PromCMS\Core\Config;

class ConfigBase {
  function __construct(array $config)
  {
    $keys = array_keys(get_class_vars(static::class));

    foreach ($keys as $key) {
      if (isset($config[$key])) {
        $this->{$key} = $config[$key];
      }
    }
  }
}