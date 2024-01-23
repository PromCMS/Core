<?php

namespace PromCMS\Core\Database\Models\Trait;

trait Localized
{
  public function getTranslations()
  {
    return $this->translations;
  }
}