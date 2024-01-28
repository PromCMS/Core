<?php

namespace PromCMS\Cli\Templates\Models\EnumTemplate;

final class EnumTemplateItem
{
  public function __construct(public readonly string $key, public readonly string $value)
  {
  }
}