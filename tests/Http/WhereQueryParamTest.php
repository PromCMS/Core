<?php

use PHPUnit\Framework\TestCase;
use PromCMS\Core\Http\WhereQueryParam;

final class WhereQueryParamTest extends TestCase
{
  public function testRejectsUnsafeFieldIdentifiers(): void
  {
    $where = new WhereQueryParam('id OR 1=1.=.1;state.=.active');

    $this->assertArrayNotHasKey('id OR 1=1', $where->parsed);
    $this->assertSame([
      'value' => 'active',
      'criteria' => '=',
    ], $where->parsed['state']);
  }
}
