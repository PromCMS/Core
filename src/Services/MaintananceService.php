<?php

namespace PromCMS\Core\Services;

use DI\Container;
use Doctrine\ORM\NoResultException;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Models\Setting;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
enum MaintananceServiceKeys
{
  case ENABLED;
  case TITLE;
  case DESCRIPTION;
  case COUNTDOWN;

  public function asString(): string
  {
    return MaintananceServiceKeys::getPrefix() . match ($this) {
      MaintananceServiceKeys::ENABLED => 'enabled',
      MaintananceServiceKeys::TITLE => 'title',
      MaintananceServiceKeys::DESCRIPTION => 'description',
      MaintananceServiceKeys::COUNTDOWN => 'countdown',
    };
  }

  public static function getPrefix(): string
  {
    return "__prom_maintanance_";
  }
}

/**
 * Manage maintanance mode 
 */
class MaintananceService
{
  private array|null $cachedMetadata = null;
  private EntityManager $em;

  public function __construct(Container $container)
  {
    $this->em = $container->get(EntityManager::class);
  }

  function enable(array|null $metadata = null)
  {
    $items = [
      [
        'key' => MaintananceServiceKeys::ENABLED->asString(),
        'value' => true,
      ]
    ];

    $possibleKeys = array_map(fn(MaintananceServiceKeys $case) => str_replace(MaintananceServiceKeys::getPrefix(), '', $case->asString()), MaintananceServiceKeys::cases());
    foreach (($metadata ?? []) as $key => $item) {
      if (!in_array($key, $possibleKeys)) {
        continue;
      }

      $items[] = [
        'key' => MaintananceServiceKeys::getPrefix() . $key,
        'value' => $item
      ];
    }

    foreach ($items as $item) {
      $key = $item['key'];
      $value = $item['value'];
      $content = [
        'type' => match ($key) {
          MaintananceServiceKeys::ENABLED->asString() => 'boolean',
          MaintananceServiceKeys::COUNTDOWN->asString() => 'dateTime',
          default => 'textArea'
        },
        'data' => match ($key) {
          MaintananceServiceKeys::ENABLED->asString() => boolval($value),
          default => strval($value)
        },
      ];

      try {
        $this->em->createQueryBuilder()
          ->update(Setting::class, 'i')
          ->set('i.content', '?1')
          ->setParameter(1, $content)
          ->where('i.slug = ?2')
          ->setParameter(2, $key)
          ->getQuery()
          ->getSingleScalarResult();
      } catch (\Exception | NoResultException $error) {
        if ($error instanceof NoResultException) {
          $newItem = new Setting();
          $newItem
            ->setContent($content)
            ->setSlug($key)
            ->setName($key);

          $this->em->persist($newItem);
        } else {
          throw $error;
        }
      }
    }

    $this->em->flush();
    $this->cachedMetadata = null;
  }

  function disableIfCountdownIsElapsed()
  {
    $data = $this->getCountdownTimestamp();

    if (is_int($data) && $data <= time()) {
      $this->disable();
    }
  }

  function disable()
  {
    if (!$this->isEnabled()) {
      return;
    }

    try {
      $this->em->createQueryBuilder()
        ->update(Setting::class, 'i')
        ->set('i.content', '?1')
        ->setParameter(1, [
          'type' => 'boolean',
          'data' => false
        ])
        ->where('i.slug = ?2')
        ->setParameter(2, MaintananceServiceKeys::ENABLED->asString())
        ->getQuery()
        ->getSingleScalarResult();

      $this->cachedMetadata = null;
    } catch (\Exception | NoResultException $error) {
      if ($error instanceof NoResultException) {
        // No need to do anything - if it is not present then it is disabled
      } else {
        throw $error;
      }
    }
  }

  private function getMetadataFromDatabase(): array
  {
    if ($this->cachedMetadata) {
      return $this->cachedMetadata;
    }

    $values = $this->em->createQueryBuilder()
      ->select('s')
      ->from(Setting::class, 's')
      ->where('s.slug LIKE ?1')
      ->setParameter(1, MaintananceServiceKeys::getPrefix() . '%')
      ->getQuery()
      ->getResult();

    $settingsBySlugs = [];
    /** @var Setting $value */
    foreach ($values as $value) {
      $settingsBySlugs[$value->getSlug()] = $value;
    }

    $this->cachedMetadata = $settingsBySlugs;

    return $settingsBySlugs;
  }

  private function getOneByKey(MaintananceServiceKeys $key): string|null
  {
    $values = $this->getMetadataFromDatabase();

    return $values[$key->asString()] ?? null;
  }

  function isEnabled()
  {
    $value = $this->getOneByKey(MaintananceServiceKeys::ENABLED);

    return $value === '1';
  }

  function getTitle()
  {
    return $this->getOneByKey(MaintananceServiceKeys::TITLE);
  }

  function getDescription()
  {
    return $this->getOneByKey(MaintananceServiceKeys::DESCRIPTION);
  }

  function getCountdownTimestamp()
  {
    $valueAsString = $this->getOneByKey(MaintananceServiceKeys::COUNTDOWN);
    $value = strtotime($valueAsString);

    return $value ? $value : null;
  }
}