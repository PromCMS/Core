<?php

namespace PromCMS\Tests\Bootstrap;

use PHPUnit\Framework\TestCase;
use PromCMS\Core\PromConfig;

final class PromConfigTest extends TestCase
{
  private const TEST_ENV = 'PROM_TEST_DB_URI';

  protected function tearDown(): void
  {
    putenv(static::TEST_ENV);
  }

  public function test_connection_uri_with_env_prefix_is_resolved_from_environment(): void
  {
    putenv(static::TEST_ENV . '=mysql://user:secret@db.example.com:3306/app');

    $config = new PromConfig([
      'project' => [
        'name' => 'env-test',
      ],
      'database' => [
        'connections' => [
          [
            'name' => 'core',
            'uri' => '$env:' . static::TEST_ENV,
          ],
        ],
      ],
    ]);

    $connections = $config->getDatabaseConnections();
    $this->assertSame(
      'mysql://user:secret@db.example.com:3306/app',
      $connections[0]['uri'],
    );
  }

  public function test_literal_connection_uri_is_left_untouched(): void
  {
    $config = new PromConfig([
      'project' => [
        'name' => 'env-test',
      ],
      'database' => [
        'connections' => [
          [
            'name' => 'core',
            'uri' => 'pdo-sqlite:///:memory:',
          ],
        ],
      ],
    ]);

    $connections = $config->getDatabaseConnections();
    $this->assertSame('pdo-sqlite:///:memory:', $connections[0]['uri']);
  }

  public function test_env_prefix_with_missing_environment_variable_resolves_to_false(): void
  {
    putenv(static::TEST_ENV);

    $config = new PromConfig([
      'project' => [
        'name' => 'env-test',
      ],
      'database' => [
        'connections' => [
          [
            'name' => 'core',
            'uri' => '$env:' . static::TEST_ENV,
          ],
        ],
      ],
    ]);

    $connections = $config->getDatabaseConnections();
    $this->assertFalse($connections[0]['uri']);
  }
}
