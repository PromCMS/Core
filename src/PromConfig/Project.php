<?php

namespace PromCMS\Core\PromConfig;

use GuzzleHttp\Psr7\Uri;
use PromCMS\Core\PromConfig\Project\Security;

class Project
{

  public function __construct(
    public readonly string $name,
    public readonly string $slug,
    public readonly Uri $url,
    public readonly array $languages,
    public readonly Security $security
  ) {
  }

  function getDefaultLanguage(): string
  {
    return $this->languages[0];
  }
}