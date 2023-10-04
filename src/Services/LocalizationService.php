<?php

namespace PromCMS\Core\Services;

use DI\Container;
use Exception;
use PromCMS\Core\Config;
use PromCMS\Core\Models\GeneralTranslations;

class LocalizationService
{
  private Container $container;
  private array $supportedLanguages;
  private string $defaultLanguage;
  private string $currentLanguage;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $config = $this->container->get(
      Config::class,
    );

    $this->supportedLanguages = $config->i18n->languages;
    $this->defaultLanguage = $config->i18n->default;
    $this->currentLanguage = $this->defaultLanguage;
  }

  function languageIsSupported($lang)
  {
    return in_array($lang, $this->supportedLanguages);
  }

  /**
   * Gets all translations for selected language
   */
  function getTranslations($language, $includeUnknown = false)
  {
    $languageTranslations = GeneralTranslations::where(['lang', '=', $language])->orderBy(["key" => "desc"])->getMany();

    $items = [];
    foreach ($languageTranslations as $item) {
      $items[$item["key"]] = $item["value"];
    }

    if ($includeUnknown) {
      $otherTranslations = GeneralTranslations::where(['key', 'NOT IN', array_keys($items)])->getMany();
      foreach ($otherTranslations as $item) {
        $items[$item["key"]] = "";
      }
    }

    return $items;
  }

  function getTranslation($lang, $key)
  {
    try {
      return GeneralTranslations::where([['lang', '=', $lang], ['key', '=', $key]])->getOne();
    } catch (\Exception $e) {
      return false;
    }
  }


  function translationExists($countryCode, $key): bool
  {
    return GeneralTranslations::exists([['lang', '=', $countryCode], ['key', '=', $key]]);
  }

  function updateTranslation($language, $key, $value)
  {
    if ($language == $this->defaultLanguage) {
      return;
    }

    if (!$this->languageIsSupported($language)) {
      throw new Exception('This language is not supported');
    }

    if ($translation = $this->getTranslation($language, $key)) {
      if (strlen($value) > 0) {
        $item = $translation->update(['value' => $value]);
      } else {
        $item = $translation->delete();
      }
    } else {
      $item = GeneralTranslations::create([
        'lang' => $language,
        'key' => $key,
        'value' => $value
      ]);
    }

    return $item->getData();
  }

  function deleteTranslationKey($key)
  {
    // TODO - delete many and return deleted
    GeneralTranslations::where(["key", '=', $key])->delete();

    return true;
  }

  function getCurrentLanguage() {
    return $this->currentLanguage;
  }

  function getSupportedLanguages()
  {
    return $this->supportedLanguages;
  }

  function setCurrentLanguage(string $nextLanguage) {
    if (!$this->languageIsSupported($nextLanguage)) {
      throw new \Exception("Cannot set language '$nextLanguage' as current language as it is not supported.");
    }

    $this->currentLanguage = $nextLanguage;
  }

  function getDefaultLanguage() {
    return $this->defaultLanguage;
  }
}
