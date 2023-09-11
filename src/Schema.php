<?php

namespace PromCMS\Core;
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
   * @return true
   * @throws ValidateSchemaException
   */
  public function validate(&$data, int $checkMode) {
    $this->validator->reset();
    $this->validator->validate($data, $this->schema);

    if (!$this->validator->isValid()) {
      throw new ValidateSchemaException($this->validator->getErrors());
    }

    return true;
  }
}