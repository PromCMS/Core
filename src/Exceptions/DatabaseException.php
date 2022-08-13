<?php

namespace PromCMS\Core\Exceptions;

use Exception;

class AppException extends Exception
{
  public function __toString()
  {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }
}
