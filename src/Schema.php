<?php

namespace PromCMS\Core;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use PromCMS\Core\Exceptions\ValidateSchemaException;

class Schema {
  private object $schema;
  private Validator $validator;

  public function __construct(object $schema) {
    $this->schema = $schema;
    $this->validator = new Validator();
  }

  /**
   * Validates data with current schema
   * 
   * @return object
   * @throws ValidateSchemaException
   */
  public function validate(&$data, int $checkMode = Constraint::CHECK_MODE_APPLY_DEFAULTS) {
    $this->validator->reset();
    $this->validator->validate($data, $this->schema, $checkMode);

    if (!$this->validator->isValid()) {
      throw new ValidateSchemaException($this->validator->getErrors());
    }

    return $data;
  }

  /**
   * Recursively cast an associative array to an object
   */
  public function arrayToObjectRecursive(array $value) {
    return $this->validator->arrayToObjectRecursive($value);
  }
}