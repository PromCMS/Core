<?php

namespace PromCMS\Core;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;

/**
 * PromCMS logger, uses monolog inside. Props to them
 */
class Logger extends MonologLogger
{
  /**
   * Pushes new stream handler that points to provided file
   */
  public function pushFileHandler(string $filepath, Level|int|string|null $level = Level::Debug)
  {
    $this->pushHandler(new StreamHandler($filepath, $level));
  }
}