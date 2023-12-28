<?php

namespace PromCMS\Core\Bootstrap;

use PromCMS\Core\Config;
use PromCMS\Core\Rendering\Twig\AppExtensions;
use PromCMS\Core\Rendering\Twig\Extensions\ArrayUtilsExtension;
use PromCMS\Core\Rendering\Twig\Extensions\LocalizationExtension;
use PromCMS\Core\Rendering\Twig\Extensions\RoutesExtension;
use PromCMS\Core\Services\RenderingService;
use Slim\Views\TwigMiddleware;
use Symfony\Component\Filesystem\Path;
use Twig\Extra\Html\HtmlExtension;
use Twig\Loader\FilesystemLoader;

class Twig implements AppModuleInterface
{
  public function run($app, $container)
  {
    /** @var Config */
    $config = $container->get(Config::class);
    $appRoot = $container->get('app.root');
    $isDevelopment = $config->env->development;
    $isDebug = $config->env->debug;
    $cachePath = Path::join($appRoot, 'cache', 'twig');

    $loader = new FilesystemLoader();

    // foreach ($paths as $namespace => $path) {
    //     if (is_string($namespace)) {
    //         $loader->setPaths($path, $namespace);
    //     } else {
    //         $loader->addPath($path);
    //     }
    // }

    $twig = new RenderingService($loader, [
      'cache' => !$isDebug && !$isDevelopment ? $cachePath : false,
    ]);

    $container->set(RenderingService::class, $twig);

    // Default Twig utils provided by slim team
    $app->add(TwigMiddleware::createFromContainer($app, RenderingService::class));

    // Add twig app extension
    $twig->addExtension(new AppExtensions($container));
    $twig->addExtension(new HtmlExtension());
    $twig->addExtension(new LocalizationExtension($container));
    $twig->addExtension(new ArrayUtilsExtension($container));
    $twig->addExtension(new RoutesExtension($container));
  }
}
