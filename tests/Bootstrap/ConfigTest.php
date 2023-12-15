<?php

namespace PromCMS\Tests\Bootstrap;

use DI\Container;
use PromCMS\Tests\AppTestCase;
use PromCMS\Core\Bootstrap\Config as ConfigBootstrap;
use PromCMS\Core\Config as AppConfig;

final class ConfigTest extends AppTestCase
{
    static string $testProjectRoot;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    public function test_toArray_converts_recursive_correctly()
    {
        $container = new Container();
        $container->set('app.root', static::$testProjectRoot);
        (new ConfigBootstrap())->run(null, $container);

        $this->assertEqualsCanonicalizing($container->get(AppConfig::class)->__toArray(), [
            'app' => [
                'prefix' => '',
                'name' => 'PromCMS Test Project',
                'root' => static::$testProjectRoot,
                'url' => 'http://localhost:3004',
                'baseUrl' => 'http://localhost:3004',
            ],
            'security' => [
                'session' => [
                    // These are the defaults
                    "name" => "prom_session",
                    "lifetime" => "1 hour"
                ],
                'token' => [
                    'lifetime' => 86400,
                ],
            ],
            'db' => [
                'root' => static::$testProjectRoot . '/.database',
                'storeConfig' => [
                    'auto_cache' => false,
                    'cache_lifetime' => null,
                    'timeout' => false,
                    'primary_key' => 'id',
                    'search' => [
                        'min_length' => 2,
                        'mode' => 'or',
                        'score_key' => 'scoreKey',
                        'algorithm' => 1,
                    ],
                ],
            ],
            'env' => [
                'development' => true,
                'debug' => true,
                'env' => 'development',
            ],
            'fs' => [
                'cachePath' => static::$testProjectRoot . '/cache/files',
                'localesPath' => static::$testProjectRoot . '/locales',
                'uploadsPath' => static::$testProjectRoot . '/uploads',
            ],
            'i18n' => [
                'default' => 'en',
                'languages' => ['en'],
            ],
            'system' => [
                'modules' => [
                    'modelsFolderName' => 'Models',
                    'controllersFolderName' => 'Controllers',
                ],
                'logging' => [
                    'logFilepath' => null
                ]
            ],
        ]);
    }
}
