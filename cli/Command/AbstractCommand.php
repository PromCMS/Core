<?php

namespace PromCMS\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends Command
{
  /**
   * {@inheritDoc}
   *
   * @return void
   */
  protected function configure()
  {
    $this
      ->addOption('cwd', null, InputOption::VALUE_OPTIONAL, 'Specifies working directory for commands', getcwd());
  }
}
