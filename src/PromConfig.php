<?php

namespace PromCMS\Core;

use GuzzleHttp\Psr7\Uri;
use PromCMS\Core\PromConfig\Entity;
use Symfony\Component\Filesystem\Path;

class PromConfig
{
  private bool $isCore;
  private string $coreModelsNamespace = 'PromCMS\Core\Models';
  private array $configuration = [
    'project' => [
      'prefix' => '',
      'url' => 'http://localhost'
    ]
  ];
  private array $coreConfiguration = [];

  private array $trailingPartOfConfigFilename = ['.prom-cms', 'parsed', 'config.php'];

  public function __construct(string $applicationRoot)
  {
    $filename = Path::join($applicationRoot, ...$this->trailingPartOfConfigFilename);

    if (!file_exists($filename)) {
      throw new \Exception("Could not find parsed Prom config, please make sure that it\'s present at {$filename}");
    }

    $configurationFromFile = require $filename;
    $configurationFromFile['project'] = array_merge($this->configuration['project'], $configurationFromFile['project']);
    $this->configuration = array_merge($this->configuration, $configurationFromFile);

    if (empty($this->configuration['database']['connections'])) {
      throw new \Exception("No database connection was provided in your config, please make sure that there is atleast one at {$filename}");
    }
    
    $this->coreConfiguration = require Path::join(__DIR__, '..', ...$this->trailingPartOfConfigFilename);
    $this->isCore = $this->getProjectName() === '__prom-core';
  }

  public function getProject(): array
  {
    return $this->configuration['project'];
  }

  public function getProjectName(): string
  {
    return $this->getProject()['name'];
  }

  public function getProjectSlug(): string|null
  {
    return $this->getProject()['slug'] ?? null;
  }

  public function getProjectUri(): Uri
  {
    return new Uri($this->getProject()['url']);
  }

  public function getDatabaseConnections(): array
  {
    return $this->configuration['database']['connections'];
  }

  public function getModelNamespace() {
    if ($this->isCore) {
      return $this->coreModelsNamespace;
    }

    return "PromCMS\Modules\\" . $this->getModuleFolderName() . "\Models";
  }

  private array $cachedEntities = [];

  /**
   * @return array<int, Entity>
   */
  function getEntities()
  {
    $models = $this->getDatabaseModels();
    $singletons = $this->configuration['database']['singletons'] ?? [];
    $cachedTableNames = array_map(fn($entity) => $entity->tableName, $this->cachedEntities);

    $entities = array_merge($models, $singletons);
    $entities = array_filter($entities, fn($entity) => !in_array($entity['tableName'], $cachedTableNames));

    foreach ($entities as $entity) {
      $entity['promConfig'] = $this;
      if (empty($entity['namespace'])) {
        $entity['namespace'] = $this->getModelNamespace();
      }
      $this->cachedEntities[$entity['tableName']] = new Entity(...$entity);
    }

    return $this->cachedEntities;
  }

  private function getDatabaseModels() {
    $models = $this->configuration['database']['models'] ?? [];

    if (!$this->isCore) {
      $coreModels = array_map(function ($entity) {
        $entity['namespace'] = $this->coreModelsNamespace;
        $entity['referenceOnly'] = true;
        return $entity;
      }, $this->coreConfiguration['database']['models']);
      $models = array_merge($models, $coreModels);
    }

    return $models;
  }

  function getEntity(string $entityTableName) {
    $models = $this->getDatabaseModels();
    $singletons = $this->configuration['database']['singletons'] ?? [];
    $entities = array_merge($models, $singletons);

    foreach ($entities as $entity) {
      $tableName = $entity['tableName'];

      if ($tableName === $entityTableName) {
        if (!isset($this->cachedEntities[$tableName])) {
          $entity['promConfig'] = $this;
          if (empty($entity['namespace'])) {
            $entity['namespace'] = $this->getModelNamespace();
          }
          $this->cachedEntities[$tableName] = new Entity(...$entity);
        }
        
        return $this->cachedEntities[$tableName];
      }
    }

    return null;
  }

  public function getModuleFolderName() {
    return ucfirst(ucwords($this->getProjectSlug() ?? $this->getProjectName()));
  }
  
  public function getTableColumns(string $tableName)
  {
    $entity = $this->getEntity($tableName);

    if (!$entity) {
      return null;
    }

    return $this->getEntity($tableName)->columns;
  }
}