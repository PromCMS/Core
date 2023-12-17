<?php

namespace PromCMS\Cli\Command;

use PromCMS\Core\App;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCommand extends Command
{
  private App|null $promApp = null;

  /**
   * {@inheritDoc}
   *
   * @return void
   */
  public function __construct(string $name = null, App $app)
  {
    parent::__construct($name);

    $this->promApp = $app;
  }

  public function getPromApp(string $cwd)
  {
    if (!$this->promApp) {
      $this->promApp = new App($cwd);
      $this->promApp->init(true);
    }

    return $this->promApp;
  }

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
