<?php

declare(strict_types=1);

namespace PromCMS\Tests;

use DI\Container;
use Doctrine\ORM\EntityRepository;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Core\Database\Models\Setting;
use PromCMS\Core\Services\MaintananceService;
use PromCMS\Core\Services\MaintananceServiceKeys;

final class MaintananceServiceTest extends AppTestCase
{
  private function getMockedService(Setting|null $existingSetting = null, Container $container = new Container())
  {
    $em = $this->createMock(EntityManager::class);

    $emRepo = $this->createMock(EntityRepository::class);
    $emRepo->expects($this->any())
      ->method('findOneBy')
      ->willReturn($existingSetting);

    $em->expects($this->any())
      ->method('getRepository')
      ->willReturn($emRepo);

    $container->set(EntityManager::class, $em);

    return new MaintananceService($container);
  }

  public function testSuccessfullyEnablesWhenNonExisting()
  {
    $this->expectNotToPerformAssertions();
    $container = new Container();
    $service = $this->getMockedService(null, $container);
    /**
     * @var EntityManager|\PHPUnit\Framework\MockObject\MockObject
     */
    $mockedEm = $container->get(EntityManager::class);

    $mockedEm->expects($this->any())->method('persist')->with($this->callback(function ($inp) {
      $isSettingClassInstance = get_class($inp) === Setting::class;

      if (!$isSettingClassInstance) {
        return false;
      }

      return $inp->getContent()['data'][MaintananceServiceKeys::TITLE->value] === 'My Title';
    }));

    $service->enable(['title' => 'My Title']);
  }

  public function testSuccessfullyDisablesExistingIfEnabled()
  {
    $container = new Container();
    $existing = new Setting();
    $existing->setContent([
      'type' => 'json',
      'data' => [
        MaintananceServiceKeys::ENABLED->value => true
      ]
    ]);
    $service = $this->getMockedService($existing, $container);
    $service->disable();

    $this->assertFalse($existing->getContent()['data'][MaintananceServiceKeys::ENABLED->value]);
  }
}