<?php

namespace PromCMS\Cli\Command;

use Propel\Generator\Command\ConfigConvertCommand;

class PropelConfigConvertCommand extends ConfigConvertCommand
{
  /**
   * @var string
   */
  public const COMMAND_NAME = 'hidden:propel:build:config';

  protected function configure()
  {
    parent::configure();

    $this->setHidden(true)->setName(static::COMMAND_NAME)->setAliases([]);
  }
}