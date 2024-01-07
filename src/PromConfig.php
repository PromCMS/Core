<?php

namespace PromCMS\Core;

use GuzzleHttp\Psr7\Uri;
use PromCMS\Core\PromConfig\Entity;
use PromCMS\Core\PromConfig\Project;
use PromCMS\Core\PromConfig\Project\Security;
use PromCMS\Core\PromConfig\Project\Security\Roles;
use Symfony\Component\Filesystem\Path;

class PromConfig
{
  public bool $isCore;
  private string $coreModelsNamespace = 'PromCMS\Core\Models';
  private array $configuration = [
    'project' => [
      'url' => 'http://localhost',
      'slug' => 'prom-core',
      'languages' => ['en']
    ]
  ];
  private array $coreConfiguration = [];


  private array $trailingPartOfConfigFilename = ['.prom-cms', 'parsed', 'config.php'];

  public function __construct(readonly string $applicationRoot)
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
    $this->isCore = $this->configuration['project']['name'] === '__prom-core';
  }

  public function getProjectModuleRoot()
  {
    return Path::join($this->applicationRoot, $this->getModuleFolderName());
  }

  public function getProjectModuleModelsRoot()
  {
    return Path::join($this->getProjectModuleRoot(), 'Models');
  }

  private Project|null $cachedProject = null;
  public function getProject(): Project
  {
    if ($this->cachedProject) {
      return $this->cachedProject;
    }

    $this->configuration['project']['security']['roles'] = array_filter(
      $this->configuration['project']['security']['roles'] ?? [],
      // Make sure thats only one admin in array
      fn($role) => $role['slug'] !== 'admin'
    );

    $this->configuration['project']['security']['roles'][] = [
      'name' => 'Admin',
      'slug' => 'admin',
      'description' => 'Main user role provided by PromCMS Core module',
      'modelPermissions' => array_fill_keys($this->getEntityTableNames(), 'allow-all')
    ];

    $this->configuration['project']['security']['roles'] = new Roles($this->configuration['project']['security']['roles']);
    $this->configuration['project']['security'] = new Security(...$this->configuration['project']['security']);
    $this->configuration['project']['url'] = new Uri($this->configuration['project']['url']);

    $this->cachedProject = new Project(...$this->configuration['project']);

    return $this->cachedProject;
  }

  public function getDatabaseConnections(): array
  {
    return $this->configuration['database']['connections'];
  }

  public function getModelNamespace()
  {
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
    $singletons = $this->getDatabaseSingletons();
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

  function getDatabaseModels(bool $includeCore = true)
  {
    $models = $this->configuration['database']['models'] ?? [];

    if (!$this->isCore && $includeCore) {
      $coreModels = array_map(function ($entity) {
        $entity['namespace'] = $this->coreModelsNamespace;
        $entity['referenceOnly'] = true;
        return $entity;
      }, $this->coreConfiguration['database']['models']);

      $models = array_merge($models, $coreModels ?? []);
    }

    return $models;
  }

  function getDatabaseSingletons()
  {
    return $this->configuration['database']['singletons'] ?? [];
  }

  public function getSingletonTableNames()
  {
    return array_map(fn($entity) => $entity['tableName'], $this->getDatabaseSingletons());
  }

  private function getEntityTableNames()
  {
    return array_merge($this->getSingletonTableNames(), array_map(fn($entity) => $entity['tableName'], $this->getDatabaseModels()));
  }

  function getEntityAsArray(string $entityTableName): ?array
  {
    $models = $this->getDatabaseModels();
    $singletons = $this->getDatabaseSingletons();
    $entities = array_merge($models, $singletons);

    foreach ($entities as $entity) {
      $tableName = $entity['tableName'];

      if ($tableName === $entityTableName) {
        return $entity;
      }
    }

    return null;
  }

  function getEntity(string $entityTableName): ?Entity
  {

    $entityAsArray = $this->getEntityAsArray($entityTableName);

    if (!$entityAsArray) {
      return null;
    }

    $tableName = $entityAsArray['tableName'];

    if (!isset($this->cachedEntities[$tableName])) {
      $entityAsArray['promConfig'] = $this;
      if (empty($entityAsArray['namespace'])) {
        $entityAsArray['namespace'] = $this->getModelNamespace();
      }
      $this->cachedEntities[$tableName] = new Entity(...$entityAsArray);
    }

    return $this->cachedEntities[$tableName];
  }

  public function getModuleFolderName()
  {
    return ucfirst(ucwords($this->configuration['project']['slug'] ?? $this->configuration['project']['name']));
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