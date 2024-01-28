<?php

namespace PromCMS\Cli\Templates;

use PhpParser\PrettyPrinter;
use PhpParser\Node\Stmt;

class TemplatePrinter extends PrettyPrinter\Standard
{
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