<?php

namespace PromCMS\Core;

use JsonSchema\Validator as ValidatorBase;

class Validator extends ValidatorBase {
  /**
   * Validate simple 
   */
  function simpleValidate (array &$value, $schema = null) {
    return $this->validate($value, (object) [
      "type" => "object",
      "properties" => (object) $schema
    ]);
  }
}