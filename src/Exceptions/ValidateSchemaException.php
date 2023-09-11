<?php 

namespace PromCMS\Core\Exceptions;

class ValidateSchemaException extends AppException {
  /**
   * @var array<string>
   */
  public array $exceptions = [];

  public function __construct(array $messages,
  $code = 900404,
  \Throwable $previous = null) {
    foreach ($messages as $exception) {
      $this->exceptions[$exception['message']] = $exception['property'];
    } 

    $failedFields = implode(array_keys($this->exceptions), ", ");

    parent::__construct("Validation failed for fields: $failedFields", $code, $previous);
  }
}