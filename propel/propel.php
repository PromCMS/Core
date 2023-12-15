<?php

// This file is only for generating models for core. 
// They will be renerated from the end application

return [
  "propel" => [
    'general' => [
      'project' => 'PromCMS Core'
    ],
    'paths' => [
      'schemaDir' => "./propel",
      'phpDir' => "./src/Models",
      'phpConfDir' => "./propel/config",
      'migrationDir' => "./propel/migrations",
      'sqlDir' => "./propel/sql"
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