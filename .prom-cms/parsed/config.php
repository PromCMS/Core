<?php

$defaultModel = [
  'namespace' => 'PromCMS\Core\Database\Models',
  'partOfCore' => true,
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
      'placement' => 'main'
    ]
  ]
];

$FILES_TABLE_NAME = 'prom__files';

return [
  // This is used for testing purposes, when used as plugin the config is fetched from app
  'project' => [
    // Leave this in unchanged
    'name' => '__prom-core',
  ],
  'database' => [
    'connections' => [
      [
        'name' => 'core',
        'uri' => 'pdo-sqlite:///' . __DIR__ . '/database.sqlite',
      ]
    ],
    // Models are used int real applications though
    'models' => [
      // Files
      array_merge($defaultModel, [
        'tableName' => $FILES_TABLE_NAME,
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
            'localized' => true,
            'required' => false
          ]),
        ]
      ]),
      // Users
      array_merge($defaultModel, [
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
            'hide' => true,
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
            'name' => 'lastname',
            'type' => 'string',
            'title' => 'Last name',
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
            'type' => 'file',
            'title' => 'Avatar',
            'required' => false,
            'multiple' => false,
            'typeFilter' => 'image',
            'admin' => [
              'fieldType' => 'big-image',
              'editor' => ['width' => 6, 'placement' => 'main'],
            ],
          ]),
          array_merge($defaultColumn, [
            'name' => 'role',
            'type' => 'string',
            'title' => 'Role',
          ]),
        ]
      ]),
      // Settings
      array_merge($defaultModel, [
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
      ]),
      // Translations
      array_merge($defaultModel, [
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
      ])
    ]
  ]
];