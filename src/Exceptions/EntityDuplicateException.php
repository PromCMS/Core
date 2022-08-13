<?php
namespace PromCMS\Core\Exceptions;

class EntityDuplicateException extends AppException
{
  protected array $fields;

  public function __construct(
    $message,
    array $fields = [],
    $code = 900409,
    \Throwable $previous = null
  ) {
    $this->fields = $fields;
    parent::__construct($message, $code, $previous);
  }

  public function getFailedFields()
  {
    return $this->fields;
  }
}
