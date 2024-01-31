<?php

namespace PromCMS\Cli\Command;

use PromCMS\Cli\Application;
use PromCMS\Cli\Templates\Models\BaseModelTemplate;
use PromCMS\Cli\Templates\Models\EnumTemplate;
use PromCMS\Cli\Templates\Models\EnumTemplate\EnumTemplateItem;
use PromCMS\Cli\Templates\Models\ModelTemplate;
use PromCMS\Cli\Templates\Models\ModelTemplateMode;
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
class ModelsCreate extends AbstractCommand
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
    $modelsRoot = $promConfig->appModelsRoot;

    if (!Application::isBeingRunInsideApp()) {
      $modelsRoot = Path::join($cwd, 'src', 'Database', 'Models');
    }

    $entities = $promConfig->getEntities();

    foreach ($entities as $entity) {
      if ($entity->referenceOnly) {
        continue;
      }

      foreach (ModelTemplateMode::cases() as $mode) {
        if ($mode === ModelTemplateMode::LOCALIZED && !$entity->localized) {
          continue;
        }

        BaseModelTemplate::from($modelsRoot, $entity, $mode)->save();

        ModelTemplate::from(
          $modelsRoot,
          $entity,
          $mode
        )->save();
      }

      $enumColumns = $entity->getEnumColumns();
      foreach ($enumColumns as $column) {
        ['name' => $enumName, 'values' => $enumValues] = $column->otherMetadata['enum'];

        EnumTemplate::from($modelsRoot)
          ->setup(
            name: $enumName,
            namespace: $entity->namespace . '\\Base',
            items: array_map(fn($value, $key) => new EnumTemplateItem($key, $value), $enumValues, array_keys($enumValues))
          )
          ->save();
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
