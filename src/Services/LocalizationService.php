<?php

namespace PromCMS\Core\Services;

use DI\Container;
use Exception;
use PromCMS\Core\Config;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use PromCMS\Core\Models\Base\GeneralTranslationQuery;
use PromCMS\Core\Models\GeneralTranslation;
use PromCMS\Core\Models\GeneralTranslations;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Map\TableMap;

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

    $localizations = GeneralTranslationQuery::create()
      ->filterBy('lang', $language, Criteria::EQUAL)
      ->orderBy("key", Criteria::DESC)
      ->find();

    $items = [];
    foreach ($localizations as $item) {
      $items[$item->getKey()] = $item->getValue();
    }

    if ($includeUnknown) {
      $otherTranslations = GeneralTranslationQuery::create()
        ->filterBy("key", array_keys($items), Criteria::NOT_IN)
        ->find();

      foreach ($otherTranslations as $item) {
        $items[$item->getKey()] = "";
      }
    }

    return $items;
  }

  function getTranslation($lang, $key)
  {
    try {
      $result = GeneralTranslationQuery::create()
        ->filterByLang($lang, Criteria::EQUAL)
        ->filterByKey($key, Criteria::EQUAL)
        ->findOne();

      if (!$result) {
        throw new EntityNotFoundException();
      }

      return $result;
    } catch (Exception $e) {
      return null;
    }
  }


  function translationExists($countryCode, $key): bool
  {
    return GeneralTranslationQuery::create()
      ->filterByLang($countryCode, Criteria::EQUAL)
      ->filterByKey($key, Criteria::EQUAL)
      ->exists();
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
        $item = $translation->fromArray(['value' => $value]);
        $item->save();
      } else {
        $item = $translation;
        $translation->delete();
      }
    } else {
      $item = new GeneralTranslation();

      $item->fromArray([
        'lang' => $language,
        'key' => $key,
        'value' => $value
      ]);

      $item->save();
    }

    return $item->toArray(TableMap::TYPE_CAMELNAME);
  }

  function deleteTranslationKey($key)
  {
    // TODO - delete many and return deleted
    GeneralTranslationQuery::create()->filterByKey($key)->delete();

    return true;
  }

  function getCurrentLanguage()
  {
    return $this->currentLanguage;
  }

  function getSupportedLanguages()
  {
    return $this->supportedLanguages;
  }

  function setCurrentLanguage(string $nextLanguage)
  {
    if (!$this->languageIsSupported($nextLanguage)) {
      throw new Exception("Cannot set language '$nextLanguage' as current language as it is not supported.");
    }

    $this->currentLanguage = $nextLanguage;
  }

  function getDefaultLanguage()
  {
    return $this->defaultLanguage;
  }
}
