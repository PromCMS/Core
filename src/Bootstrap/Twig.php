<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Config;
use PromCMS\Core\Path;
use Slim\Views\Twig as TwigViews;
use Slim\Views\TwigMiddleware;

class Twig implements AppModuleInterface
{
  public function run($app, $container)
  {
    /** @var Config */
    $config = $container->get(Config::class);
    $appRoot = $config->app->root;
    $isDevelopment = $config->env->development;
    $isDebug = $config->env->debug;

    $templatesPath = Path::join($appRoot, 'templates');
    $cachePath =  Path::join($appRoot, 'cache', 'twig');

    if (!file_exists($templatesPath)) {
      if (!mkdir($templatesPath, 0777)) {
        throw new \Exception('Failed to create templates directory');
      }
    }

    $container->set('twig', function () use ($templatesPath, $cachePath, $isDevelopment, $isDebug) {
      return TwigViews::create($templatesPath, ['cache' => !$isDebug && !$isDevelopment
        ? [
          'cache' => $cachePath,
        ]
        : []]);
    });

    $app->add(TwigMiddleware::createFromContainer($app));
  }
}
