<?php

$defaultModel = [
  'timestamp' => true,
  'sorting' => false,
  'draftable' => false,
  'softDelete' => false,
  'sharable' => false,
  'ownable' => false,
  'ignoreSeeding' => true,
  'admin' => [
    'isHidden' => false,
  ]
];

$defaultColumn = [
  'required' => true,
  'unique' => false,
  'localized' => false,
  'readonly' => false,
  'hide' => false,
  'admin' => [
    'isHidden' => false,
    'editor' => [
      'width' => 12,
      'placement' => 'aside'
    ]
  ]
];

$userRoleModel = array_merge($defaultModel, [
  'tableName' => 'prom__user_roles',
  'phpName' => 'UserRole',
  'title' => 'User roles',
  'admin' => [
    'icon' => 'UserExclamation'
  ],
  'columns' => [
    array_merge($defaultColumn, [
      'name' => 'label',
      'type' => 'string',
      'title' => 'Label',
      'unique' => true
    ]),
    array_merge($defaultColumn, [
      'name' => 'permissions',
      'type' => 'json',
      'title' => 'Permissions',
      'defaultValue' => '[]',
      'required' => false
    ]),
    array_merge($defaultColumn, [
      'name' => 'description',
      'type' => 'longText',
      'title' => 'Description',
      'required' => false
    ]),
  ]
]);

$fileModel = array_merge($defaultModel, [
  'tableName' => 'prom__files',
  'phpName' => 'File',
  'title' => 'Files',
  'admin' => [
    'icon' => 'Folder'
  ],
  'columns' => [
    array_merge($defaultColumn, [
      'name' => 'filename',
      'type' => 'string',
      'title' => 'Filename',
    ]),
    array_merge($defaultColumn, [
      'name' => 'mimeType',
      'type' => 'string',
      'title' => 'Mime type',
    ]),
    array_merge($defaultColumn, [
      'name' => 'filepath',
      'type' => 'longText',
      'title' => 'Filepath',
    ]),
    array_merge($defaultColumn, [
      'name' => 'private',
      'type' => 'boolean',
      'title' => 'Private',
      'defaultValue' => 'false',
      'required' => false
    ]),
    array_merge($defaultColumn, [
      'name' => 'description',
      'type' => 'longText',
      'title' => 'Description',
      'required' => false
    ]),
  ]
]);

$usersModel = array_merge($defaultModel, [
  'tableName' => 'prom__users',
  'phpName' => 'User',
  'title' => 'Users',
  'ignoreSeeding' => false,
  'admin' => [
    'icon' => 'Archive'
  ],
  'columns' => [
    array_merge($defaultColumn, [
      'name' => 'email',
      'type' => 'string',
      'title' => 'Email',
      'unique' => true,
    ]),
    array_merge($defaultColumn, [
      'name' => 'password',
      'type' => 'longText',
      'title' => 'Password',
      'editable' => false,
      'admin' => array_merge($defaultColumn['admin'], [
        'isHidden' => true
      ])
    ]),
    array_merge($defaultColumn, [
      'name' => 'firstname',
      'type' => 'string',
      'title' => 'First name',
    ]),
    array_merge($defaultColumn, [
      'name' => 'state',
      'type' => 'enum',
      'title' => 'State',
      'enum' => [
        'name' => 'UserState',
        'values' => [
          'ACTIVE' => 'active',
          'INVITED' => 'invited',
          'BLOCKED' => 'blocked',
          'PASSWORD_RESET' => 'password-reset',
        ]
      ],
      'defaultValue' => 'UserState::INVITED'
    ]),
    array_merge($defaultColumn, [
      'name' => 'avatar',
      'type' => 'relationship',
      'title' => 'Avatar',
      'required' => false,
      'targetModelTableName' => $fileModel['tableName'],
      'labelConstructor' => '{{id}}',
      'multiple' => false,
      'foreignKey' => 'id'
    ]),
    array_merge($defaultColumn, [
      'name' => 'role',
      'type' => 'relationship',
      'title' => 'Role',
      'required' => false,
      'targetModelTableName' => $userRoleModel['tableName'],
      'labelConstructor' => '{{id}}',
      'multiple' => false,
      'foreignKey' => 'id'
    ]),
  ]
]);

$optionsModel = array_merge($defaultModel, [
  'tableName' => 'prom__settings',
  'title' => 'Settings',
  'phpName' => 'Setting',
  'ownable' => true,
  'admin' => [
    'icon' => 'Settings'
  ],
  'columns' => [
    array_merge($defaultColumn, [
      'name' => 'name',
      'type' => 'string',
      'title' => 'Name',
      'unique' => true
    ]),
    array_merge($defaultColumn, [
      'name' => 'content',
      'type' => 'json',
      'title' => 'Content',
      'defaultValue' => '[]',
      'required' => false
    ]),
    array_merge($defaultColumn, [
      'name' => 'description',
      'type' => 'longText',
      'title' => 'Description',
      'required' => false
    ]),
  ]
]);

$generalTranslationsModel = array_merge($defaultModel, [
  'tableName' => 'prom__general_translations',
  'title' => 'General translations',
  'phpName' => 'GeneralTranslation',
  'admin' => [
    'icon' => 'LanguageHiragana'
  ],
  'columns' => [
    array_merge($defaultColumn, [
      'name' => 'lang',
      'type' => 'string',
      'title' => 'Language',
      'unique' => 'prom__general_translations_unique'
    ]),
    array_merge($defaultColumn, [
      'name' => 'key',
      'type' => 'string',
      'title' => 'Key',
      'unique' => 'prom__general_translations_unique'
    ]),
    array_merge($defaultColumn, [
      'name' => 'value',
      'type' => 'string',
      'title' => 'Value',
    ]),
  ]
]);

return [
  // This is used for testing purposes, when used as plugin the config is fetched from app
  'project' => [
    // Leave this in unchanged
    'name' => '__prom-core',
  ],
  'database' => [
    'connections' => [
      'core' => [
        'driver' => 'pdo_sqlite',
        'path' => __DIR__ . '/database.sqlite',
      ]
    ],
    // Models are used int real applications though
    'models' => [
      $usersModel,
      $userRoleModel,
      $fileModel,
      $optionsModel,
      $generalTranslationsModel
    ]
  ]
];