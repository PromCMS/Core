<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Config;
use PromCMS\Core\Path;
use PromCMS\Core\Rendering\Twig\AppExtensions;
use PromCMS\Core\Services\RenderingService;
use Slim\Views\TwigMiddleware;
use Twig\Extra\Html\HtmlExtension;

class Twig implements AppModuleInterface
{
  public function run($app, $container)
  {
    /** @var Config */
    $config = $container->get(Config::class);
    $appRoot = $config->app->root;
    $isDevelopment = $config->env->development;
    $isDebug = $config->env->debug;

    $defaultViewsPath = Path::join($appRoot, 'Views');
    $cachePath =  Path::join($appRoot, 'cache', 'twig');

    if (!file_exists($defaultViewsPath)) {
      if (!mkdir($defaultViewsPath, 0777)) {
        throw new \Exception('Failed to create templates directory');
      }
    }

    $twig = RenderingService::create([$defaultViewsPath], [
      'cache' => !$isDebug && !$isDevelopment ? $cachePath : false,
    ]);

    $container->set(RenderingService::class, $twig);
    
    // Default Twig utils provided by slim team
    $app->add(TwigMiddleware::createFromContainer($app, TwigViews::class));

    // Add twig app extension
    $twig->addExtension(new AppExtensions($container));
    $twig->addExtension(new HtmlExtension());
  }
}
