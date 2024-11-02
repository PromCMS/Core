<?php

namespace PromCMS\Cli\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

#[
  AsCommand(
    name: 'migration:apply',
    description: 'Uses created models definition and sets it to database',
    hidden: false
  )
]
class MigrationApply extends AbstractCommand
{
  /**
   * {@inheritDoc}
   *
   * @return void
   */
  protected function configure(): void
  {
    parent::configure();
  }

  private function getConfigPath(string $root): string
  {
    return Path::join($root, '.prom-cms', 'parsed', 'config.php');
  }

  private function hasConfigDefined(string $root): bool
  {
    return file_exists($this->getConfigPath($root));
  }

  /**
   * {@inheritDoc}
   *
   * @return void
   */
  protected function initialize(
    InputInterface $input,
    OutputInterface $output
  ): void {
    $cwd = $input->getOption('cwd');

    if (!$this->hasConfigDefined($cwd)) {
      throw new \Exception("No config file at the root of $cwd has been found");
    }
  }

  /**
   * {@inheritDoc}
   *
   */
  protected function execute(
    InputInterface $input,
    OutputInterface $output
  ): int {
    $cwd = $input->getOption('cwd');
    chdir($cwd);
    $ormSchemaToolUpdateInput = new ArrayInput([
      'command' => 'orm:schema-tool:update',
      '--force' => true,
      '--complete' => true,
      '--dump-sql' => true,
    ]);

    $ormSchemaToolUpdate = $this->getApplication()->doRun(
      $ormSchemaToolUpdateInput,
      $output
    );

    return $ormSchemaToolUpdate && $this::SUCCESS;
  }
}
