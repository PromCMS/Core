<?php

use DI\Container;
use PromCMS\Core\Config;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use PromCMS\Core\Path;

return function (Container $container) {
  /** @var Config */
  $config = $container->get(Config::class);
  $appRoot = $config->app->root;
  $isDevelopment = $config->env->development;
  $isDebug = $config->env->debug;

  $templatesPath = Path::join($appRoot, 'templates');
  $cachePath =  Path::join($appRoot, 'cache','twig');

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
};
