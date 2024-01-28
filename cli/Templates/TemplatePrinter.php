<?php

namespace PromCMS\Cli\Templates;

use PhpParser\PrettyPrinter;
use PhpParser\Node\Stmt;

class TemplatePrinter extends PrettyPrinter\Standard
{
  protected function indent(): void
  {
    $this->indentLevel += 2;
    $this->nl .= '  ';
  }

  protected function outdent(): void
  {
    assert($this->indentLevel >= 2);
    $this->indentLevel -= 2;
    $this->nl = $this->newline . str_repeat(' ', $this->indentLevel);
  }


  protected function pStmt_Property(Stmt\Property $node): string
  {
    $output = parent::pStmt_Property($node);

    return $this->nl . $output;
  }

  protected function pStmt_ClassMethod(Stmt\ClassMethod $node): string
  {
    $output = parent::pStmt_ClassMethod($node);

    return $this->nl . $output;
  }

  protected function pStmt_Class(Stmt\Class_ $node): string
  {
    $output = parent::pStmt_Class($node);

    return $this->nl . $output;
  }
}