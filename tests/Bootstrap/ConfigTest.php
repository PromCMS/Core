<?php

namespace PromCMS\Tests\Bootstrap;

use DI\Container;
use PromCMS\Tests\AppTestCase;
use PromCMS\Core\Internal\Bootstrap\Config as ConfigBootstrap;
use PromCMS\Core\Config as AppConfig;
use Symfony\Component\Filesystem\Path;

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
        $container->set('core.root', Path::join(__DIR__, "..", ".."));
        (new ConfigBootstrap())->run(null, $container);

        $this->assertEqualsCanonicalizing($container->get(AppConfig::class)->__toArray(), [
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
            'env' => [
                'development' => true,
                'debug' => true,
                'env' => 'development',
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
