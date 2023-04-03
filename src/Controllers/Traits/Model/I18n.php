<?php

namespace PromCMS\Core\Controllers\Traits\Model;

use PromCMS\Core\Config\i18n as i18nConfig;

trait I18n
{
  protected i18nConfig $languageConfig;

  private function getCurrentLanguage($request, $args)
  {
    $queryParams = $request->getQueryParams();
    $nextLanguage = $this->languageConfig->default;
    $supportedLanguages = $this->languageConfig->languages;

    if (
      isset($queryParams['lang']) &&
      in_array($queryParams['lang'], $supportedLanguages)
    ) {
      $nextLanguage = $queryParams['lang'];
    }

    if (isset($args['language'])) {
      $nextLanguage = $args['language'];
    }

    return $nextLanguage;
  }
}
