<?php

namespace PromCMS\Core;

use GuzzleHttp\Psr7\Uri;
use Symfony\Component\Filesystem\Path;

class PromConfig
{
  private array $configuration = [
    'project' => [
      'prefix' => '',
      'url' => 'http://localhost'
    ]
  ];

  private array $trailingPartOfConfigFilename = ['.prom-cms', 'parsed', 'config.php'];

  public function __construct(string $applicationRoot)
  {
    $filename = Path::join($applicationRoot, ...$this->trailingPartOfConfigFilename);

    if (!file_exists($filename)) {
      throw new \Exception("Could not find parsed Prom config, please make sure that it\'s present at {$filename}");
    }

    $configurationFromFile = require_once $filename;
    $this->configuration = array_merge_recursive($this->configuration, $configurationFromFile);

    if (empty($this->configuration['database']['connections'])) {
      throw new \Exception("No database connection was provided in your config, please make sure that there is atleast one at {$filename}");
    }
  }

  public function getProject(): array
  {
    return $this->configuration['project'];
  }

  public function getProjectName(): string
  {
    return $this->getProject()['name'];
  }

  public function getProjectUri(): Uri
  {
    return new Uri($this->getProject()['url']);
  }

  public function getDatabaseConnections(): array
  {
    return $this->configuration['database']['connections'];
  }

  function getModels(): array
  {
    return $this->configuration['database']['models'] ?? [];
  }

  function getSingletons(): array
  {
    return $this->configuration['database']['singletons'] ?? [];
  }

  public function getTableColumns(string $tableName): array|null
  {
    $models = $this->getModels();
    $singletons = $this->getSingletons();
    $modelsAndSingletons = array_merge($models, $singletons);

    foreach ($modelsAndSingletons as $entity) {
      if ($entity['tableName'] === $tableName) {
        return $entity['columns'] ?? [];
      }
    }

    return null;
  }
}