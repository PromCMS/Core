<?php

namespace PromCMS\Core\Database\Models\Trait;

use Doctrine\Common\Collections\ArrayCollection;

trait Localized
{
  public function getTranslations(): ArrayCollection
  {
    $result = [];
    foreach ($this->translations as $translation) {
      $result[$translation['locale']] = $translation;
    }

    return new ArrayCollection($result);
  }
}