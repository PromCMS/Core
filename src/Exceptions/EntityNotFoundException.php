<?php

namespace PromCMS\Core\Exceptions;

class EntityNotFoundException extends AppException
{
  public function __construct(
    $message = 'Item not found',
    $code = 900404,
    \Throwable $previous = null
  ) {
    parent::__construct($message, $code, $previous);
  }
}
