<?php

// This is used for testing purposes, when used as plugin the config is fetched from app
return [
  'project' => [
    'name' => 'PromCMS Project',
  ],
  'database' => [
    'connections' => [
      'core' => [
        'driver' => 'pdo_sqlite',
        'path' => __DIR__ . '/database.sqlite',
      ]
    ]
  ]
];