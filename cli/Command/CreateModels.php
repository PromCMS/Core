<?php

namespace PromCMS\Cli\Command;

use PromCMS\Cli\Application;
use PromCMS\Cli\Template;
use PromCMS\Core\PromConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

#[AsCommand(
  name: 'models:create',
  description: 'Creates models defined in prom config.',
  hidden: false,
)]
class CreateModels extends AbstractCommand
{
  /**
   * {@inheritDoc}
   *
   * @return void
   */
  protected function configure()
  {
    parent::configure();
  }

  private function getConfigPath(string $root)
  {
    return Path::join($root, '.prom-cms', 'parsed', 'config.php');
  }

  private function hasConfigDefined(string $root)
  {
    return file_exists($this->getConfigPath($root));
  }

  /**
   * {@inheritDoc}
   *
   * @return void
   */
  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    $cwd = $input->getOption('cwd');

    if (!$this->hasConfigDefined($cwd)) {
      throw new \Exception("No config file at the root of $cwd has been found");
    }
  }

  private function snakecase(string $input)
  {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
  }

  /**
   * {@inheritDoc}
   *
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $cwd = $input->getOption('cwd');
    chdir($cwd);

    $promConfig = new PromConfig($cwd);
    $modelsRoot = Path::join($cwd, 'Modules', $promConfig->getModuleFolderName(), 'Models');

    if (!Application::isBeingRunInsideApp()) {
      $modelsRoot = Path::join($cwd, 'src', 'Models');
    }

    $entities = $promConfig->getEntities();
    $templatesRoot = Path::join(__DIR__, '..', 'templates');

    foreach ($entities as $entity) {
      if ($entity->referenceOnly) {
        continue;
      }

      $modalClassName = $entity->phpName;
      $modelFilename = "$modalClassName.php";
      $templateVars = [
        'entity' => $entity
      ];
      $baseFoldername = Path::join($modelsRoot, 'Base');

      $mutableFilaname = Path::join($modelsRoot, $modelFilename);
      if (!file_exists($mutableFilaname)) {
        Template::create(Path::join($templatesRoot, 'generic.model.php'))->renderTo($mutableFilaname, $templateVars);
      }

      Template::create(Path::join($templatesRoot, 'base.model.php'))->renderTo(Path::join($baseFoldername, $modelFilename), $templateVars);

      $enumColumns = $entity->getEnumColumns();
      foreach ($enumColumns as $column) {
        $phpName = $column->getPhpType();

        Template::create(Path::join($templatesRoot, 'generic.enum.php'))->renderTo(Path::join($baseFoldername, "$phpName.php"), [
          'enum' => $column->otherMetadata['enum'],
          'entity' => $entity
        ]);
      }
    }

    $ormSchemaToolUpdateInput = new ArrayInput([
      'command' => 'orm:schema-tool:update',
      '--force' => true,
      '--complete' => true,
    ]);

    $ormSchemaToolUpdate = $this->getApplication()->doRun($ormSchemaToolUpdateInput, $output);

    return $ormSchemaToolUpdate && $this::SUCCESS;
  }
}
