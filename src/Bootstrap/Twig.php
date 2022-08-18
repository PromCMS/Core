<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Config;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use PromCMS\Core\Path;

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

    $twigLoader = new FilesystemLoader($templatesPath);
    $twig = new Environment(
      $twigLoader,
      !$isDebug && !$isDevelopment
        ? [
          'cache' => $cachePath,
        ]
        : [],
    );

    $container->set('twig', $twig);
  }
}
