<?php

namespace PromCMS\Core;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use PromCMS\Core\Exceptions\ValidateSchemaException;

class Schema {
  private object $schema;
  private Validator $validator;

  public function __construct(object|array $schema) {
    $this->validator = new Validator();
    $this->schema = is_array($schema) ? $this->arrayToObjectRecursive($schema) : $schema;
  }

  /**
   * Validates data with current schema
   * 
   * @return object
   * @throws ValidateSchemaException
   */
  public function validate($data, int $checkMode = Constraint::CHECK_MODE_APPLY_DEFAULTS) {
    $incomingDataIsObject = is_object($data);
    $result = $incomingDataIsObject ? $data : $this->arrayToObjectRecursive($data);
    $this->validator->reset();
    $this->validator->validate($result, $this->schema, $checkMode);

    if (!$this->validator->isValid()) {
      throw new ValidateSchemaException($this->validator->getErrors());
    }

    return $incomingDataIsObject ? $result : $this->objectToArrayRecursive($result);
  }

  /**
   * Recursively cast an associative array to an object
   */
  public function arrayToObjectRecursive(array $value) {
    return $this->validator->arrayToObjectRecursive($value);
  }

  public function objectToArrayRecursive(mixed $value) {
      if(is_object($value) || is_array($value)) {
          $ret = (array) $value;
          foreach ($ret as &$item) {
              $item = $this->objectToArrayRecursive($item);
          }
          return $ret;
      } else {
          return $value;
      }
  }
}