<?php

namespace PromCMS\Core\Exceptions;

use Exception;

class AppException extends Exception
{
  public function __toString()
  {
    return get_class($this) . ": [{$this->code}]: {$this->message}\n";
  }
}
