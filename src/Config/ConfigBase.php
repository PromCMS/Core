<?php
namespace PromCMS\Core\Config;

class ConfigBase {
  function __construct(array $config)
  {
    $keys = array_keys(get_object_vars($this));

    foreach ($keys as $key) {
      if (isset($config[$key])) {
        $this->{$key} = $config[$key];
      }
    }
  }
}