<?php

namespace PromCMS\Tests\Services;

use PHPUnit\Framework\TestCase;
use PromCMS\Core\Services\PasswordService;


final class PasswordServiceTest extends TestCase
{
  public function testShouldWorkBothWays()
  {
    $passwordService = new PasswordService();

    $password = "test_test_123";
    $hashed = $passwordService->generate($password);

    $this->assertEquals(true, $passwordService->validate($password, $hashed));
  }
}
