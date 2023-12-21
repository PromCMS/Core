<?php

// This file is only for generating models for core. 
// They will be renerated from the end application

return [
  "propel" => [
    'general' => [
      'project' => 'PromCMS Core'
    ],
    'paths' => [
      'schemaDir' => "./.prom-cms/propel",
      'phpDir' => "./src/Models",
      'phpConfDir' => "./.prom-cms/propel/config",
      'migrationDir' => "./.prom-cms/propel/migrations",
      'sqlDir' => "./.prom-cms/propel/sql"
    ],
    'generator' => [
      'namespaceAutoPackage' => false,
      'defaultConnection' => 'core',
      'connections' => ['core']
    ],
    'runtime' => [
      'defaultConnection' => 'core',
      'connections' => ['core']
    ],
    'database' => [
      'connections' => [
        'core' => [
          'adapter' => "sqlite",
          'dsn' => "sqlite:" . __DIR__ . "/db.sq3",
          'user' => "core",
          'password' => null,
          'settings' => [
            'charset' => "utf8"
          ]
        ]
      ]
    ],
  ]
];