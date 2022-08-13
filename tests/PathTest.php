<?php declare(strict_types=1);

namespace PromCMS\Tests;
use PHPUnit\Framework\TestCase;
use PromCMS\Core\Path;

final class PathTest extends TestCase 
{

  public function testJoinsPathsRight(): void {
    $separator = DIRECTORY_SEPARATOR;
    $rightPath = "test" . $separator . "something";

    echo Path::join("test", "something");

    $this->expectOutputString($rightPath);
  }
}