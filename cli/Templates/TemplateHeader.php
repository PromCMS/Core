<?php

namespace PromCMS\Cli\Templates;

use PhpParser\Comment;

class TemplateHeader
{
  private array $lines = [];

  function addLine(string $line)
  {
    $this->lines[] = $line;
    return $this;
  }

  function __toString()
  {
    if (!count($this->lines)) {
      return '';
    }

    return "/**\n" . implode("\n", array_map(fn($item) => " * $item", $this->lines)) . "\n */\n";
  }

  function toExpression()
  {
    if (!count($this->lines)) {
      return null;
    }

    return new Comment(
      text: $this->__toString(),
    );
  }
}