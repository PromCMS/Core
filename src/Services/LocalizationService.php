<?php

namespace PromCMS\Core\Services;

use DI\Container;
use Doctrine\ORM\QueryBuilder;
use Exception;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Exceptions\EntityNotFoundException;
use PromCMS\Core\Models\GeneralTranslation;
use PromCMS\Core\PromConfig;

class LocalizationService
{
  private Container $container;
  private array $supportedLanguages;
  private string $defaultLanguage;
  private string $currentLanguage;
  private EntityManager $em;
  private QueryBuilder $qb;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $promConfig = $this->container->get(
      PromConfig::class,
    );

    $this->supportedLanguages = $promConfig->getProject()->languages;
    $this->defaultLanguage = $promConfig->getProject()->getDefaultLanguage();
    $this->currentLanguage = $this->defaultLanguage;
    $this->em = $container->get(EntityManager::class);
    $this->qb = $this->em->createQueryBuilder();
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
    $r = $this->em->getRepository(GeneralTranslation::class);
    $localizations = $r
      ->findBy(
        [
          'lang' => $language,
        ],
        [
          'key' => "DESC"
        ]
      );

    $items = [];
    /**
     * @var GeneralTranslation $item
     */
    foreach ($localizations as $item) {
      $items[$item->getKey()] = $item->getValue();
    }

    if ($includeUnknown) {
      $otherTranslations = $this->qb->from(GeneralTranslation::class, 't')
        ->where($this->qb->expr()->notIn('t.key', array_keys($items)))
        ->getQuery()
        ->execute();

      foreach ($otherTranslations as $item) {
        $items[$item->getKey()] = "";
      }
    }

    return $items;
  }

  function getTranslation($lang, $key): ?GeneralTranslation
  {
    $r = $this->em->getRepository(GeneralTranslation::class);

    try {
      $result = $r->findOneBy([
        'lang' => $lang,
        'key' => $key
      ]);

      if (!$result) {
        throw new EntityNotFoundException();
      }

      return $result;
    } catch (Exception $e) {
      return null;
    }
  }


  function translationExists($lang, $key): bool
  {
    try {
      $this->getTranslation($lang, $key);
      return true;
    } catch (Exception $error) {
      return false;
    }
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
        $item = $translation->setValue($value);
      } else {
        $item = $translation;

        $this->em->remove($translation);
      }
    } else {
      $item = new GeneralTranslation();

      $item->fill([
        'lang' => $language,
        'key' => $key,
        'value' => $value
      ]);
    }

    $this->em->flush();

    return $item->toArray();
  }

  function deleteTranslationKey($key)
  {
    $this->qb->delete(GeneralTranslation::class, 't')->where('t.key = :key')->setParameter('key', $key)->getQuery()->execute();

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
