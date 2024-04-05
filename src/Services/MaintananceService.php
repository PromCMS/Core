<?php

namespace PromCMS\Core\Services;

use DI\Container;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Models\Setting;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
enum MaintananceServiceKeys: string
{
  case ENABLED = 'enabled';
  case TITLE = 'title';
  case DESCRIPTION = 'description';
  case COUNTDOWN = 'countdown';
}

/**
 * Manage maintanance mode 
 */
class MaintananceService
{
  private string $SETTING_KEY = '__prom_maintanance';
  private EntityManager $em;

  public function __construct(Container $container)
  {
    $this->em = $container->get(EntityManager::class);
  }

  function enable(array|null $metadata = [])
  {
    $metadata = $metadata ?? [];
    $existing = $this->getDataFromDatabase();

    if (!$existing) {
      $existing = new Setting();
      $existing
        ->setName($this->SETTING_KEY)
        ->setSlug($this->SETTING_KEY)
        ->setContent([
          'type' => 'json',
          'data' => []
        ]);
    }

    $newMetadata = array_merge($existing->getContent()['data'] ?? [], [
      MaintananceServiceKeys::ENABLED->value => true
    ]);

    foreach ($metadata as $key => $item) {
      try {
        $keyAsEnum = MaintananceServiceKeys::from($key);
      } catch (\Exception $error) {
        // No need to throw
        continue;
      }

      $newMetadata[$keyAsEnum->value] = match ($keyAsEnum) {
        MaintananceServiceKeys::ENABLED => boolval($item),
        MaintananceServiceKeys::COUNTDOWN => ($valueToTime = strtotime(strval($item))) ? $valueToTime : null,
        default => strval($item)
      };
    }

    $existing->setContent([
      'type' => 'json',
      'data' => $newMetadata
    ]);

    $this->em->persist($existing);
    $this->em->flush();
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

    // item wont be null as isEnabled returns false if item does not exist yet
    $item = $this->getDataFromDatabase();
    $content = $item->getContent();
    $item->setContent(
      array_merge(
        $content,
        [
          'data' => array_merge(
            $content['data'],
            [
              MaintananceServiceKeys::ENABLED->value => false
            ]
          )
        ]
      )
    );
    $this->em->flush();
  }

  private function getDataFromDatabase(): Setting|null
  {
    return $this->em->getRepository(Setting::class)->findOneBy([
      'slug' => $this->SETTING_KEY
    ]);
  }

  /**
   * @return array<string, string|bool>
   */
  private function extractMetadata(Setting|null $item): array
  {
    if (!$item) {
      return [];
    }

    $metadata = $item->getContent()['data'] ?? [];
    $settingsBySlugs = [];
    foreach ($metadata as $key => $value) {
      try {
        $keyAsEnum = MaintananceServiceKeys::from($key);
      } catch (\Exception $error) {
        // No need to throw
        continue;
      }

      $settingsBySlugs[$keyAsEnum->value] = $value;
    }

    return $settingsBySlugs;
  }

  private function getOneByKey(MaintananceServiceKeys $key): string|int|bool|null
  {
    $values = $this->extractMetadata($this->getDataFromDatabase());

    return $values[$key->value] ?? null;
  }

  function isEnabled()
  {
    $value = $this->getOneByKey(MaintananceServiceKeys::ENABLED);

    return $value === true;
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
    $value = $this->getOneByKey(MaintananceServiceKeys::COUNTDOWN);
    if (!$value) {
      return null;
    }

    $value = !is_int($value) ? strtotime($value) : $value;

    return $value ? $value : null;
  }
}