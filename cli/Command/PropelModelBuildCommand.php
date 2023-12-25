<?php

namespace PromCMS\Cli\Command;

use Propel\Generator\Command\ModelBuildCommand;

class PropelModelBuildCommand extends ModelBuildCommand
{
  /**
   * @var string
   */
  public const COMMAND_NAME = 'hidden:propel:build:model';

  protected function configure()
  {
    parent::configure();

    $this->setHidden(true)->setName(static::COMMAND_NAME)->setAliases([]);
  }
}



