<?php

namespace PromCMS\Core\Http;

enum ContentType
{
  case JSON;
  case HTML;

  public function asString(): string
  {
    return match ($this) {
      ContentType::JSON => 'json',
      ContentType::HTML => 'html',
    };
  }

  public function asHeaderValue(): string
  {
    return match ($this) {
      ContentType::JSON => 'application/json',
      ContentType::HTML => 'text/html',
    };
  }
}