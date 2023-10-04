<?php

namespace PromCMS\Core\Rendering\Twig\Extensions;

use DI\Container;
use PromCMS\Core\Config;
use PromCMS\Core\Services\LocalizationService;
use PromCMS\Core\Services\RenderingService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LocalizationExtension extends AbstractExtension
{
  private RenderingService $twigService;
  private Config $config;
  private LocalizationService $localizationService;
  private string $currentLanguage;
  private array $cachedTranslations;

  public function __construct(Container $container)
  {
    $this->twigService = $container->get(RenderingService::class);
    $this->config = $container->get(Config::class);
    $this->localizationService = $container->get(LocalizationService::class);
    $this->cachedTranslations = [];
  }

  public function getFilters()
  {
    return [
      new TwigFilter('t', [$this, 'translate']),
    ];
  }

  public function translate($value) {
    $currentLanguage = $this->localizationService->getCurrentLanguage();

    if (!isset($cachedTranslations[$currentLanguage])) {
      $cachedTranslations[$currentLanguage] = $this->localizationService
        ->getTranslations($currentLanguage, false);
    }

    return isset($cachedTranslations[$currentLanguage][$value]) 
      ? $cachedTranslations[$currentLanguage][$value] 
      : $value;
  }
}