<?php
if (!class_exists(\Symfony\Component\Console\Application::class)) {
    $autoloadFileCandidates = [
        __DIR__ . '/../../../autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/../vendor/autoload.php.dist',
    ];
    foreach ($autoloadFileCandidates as $file) {
        if (file_exists($file)) {
            require_once $file;
            
            break;
        }
    }
}

use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Path;
use PromCMS\Core\Database\EntityManager;
use PromCMS\Cli\Application;
use PromCMS\Core\App;

$promAppRoot = Application::getPromCoreRoot();
if (!file_exists($promAppRoot)) {
    mkdir($promAppRoot);
}

$cliApp = new Application('prom-cms');
$promApp = new App(Application::isBeingRunInsideApp() ? Path::join($promAppRoot, '..', '..') : $promAppRoot);
$ns = '\\PromCMS\\Cli\\Command\\';

$finder = new Finder();
$finder->files()->name('*.php')->in(__DIR__ . '/../cli/Command')->depth(0);
foreach ($finder as $file) {
    $r = new \ReflectionClass($ns . $file->getBasename('.php'));
    if ($r->isSubclassOf(Command::class) && !$r->isAbstract()) {
        $cliApp->add($r->newInstance(null));
    }
}

$promApp->init(true);
$em = $promApp->getSlimApp()->getContainer()->get(EntityManager::class);
ConsoleRunner::addCommands($cliApp, new SingleManagerProvider($em));

$cliApp->run();
