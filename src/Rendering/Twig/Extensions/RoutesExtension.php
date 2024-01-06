<?php

namespace PromCMS\Core\Rendering\Twig\Extensions;

use DI\Container;
use PromCMS\Core\PromConfig;
use PromCMS\Core\Services\LocalizationService;
use PromCMS\Core\Services\RouteCollectorService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RoutesExtension extends AbstractExtension
{
  private PromConfig $promConfig;
  private Container $container;
  private LocalizationService $localizationService;
  private string $currentLanguage;
  private array $cachedTranslations;

  public function __construct(Container $container)
  {
    $this->container = $container;
    $this->promConfig = $this->container->get(PromConfig::class);
    $this->localizationService = $this->container->get(LocalizationService::class);
    $this->cachedTranslations = [];
  }

  public function getFunctions()
  {
    return [
      new TwigFunction('url_for', [$this, 'urlFor']),
    ];
  }

  // Override to slim twig extension
  public function urlFor(string $routeName, array $data = [], array $queryParams = []): string
    {
        $currentLanguage = $this->localizationService->getCurrentLanguage();
        $defaultLanguage = $this->promConfig->getProjectDefaultLanguage();
        $finalRoute = $this->container
          ->get(RouteCollectorService::class)
          ->getRouteParser()
          ->urlFor($routeName, $data, $queryParams);

        // if current language is not the same as default one then we prepend current language
        if ($currentLanguage !== $defaultLanguage) {
            $finalRoute = "/$currentLanguage" . $finalRoute;
        }

        return $finalRoute;
    }
}