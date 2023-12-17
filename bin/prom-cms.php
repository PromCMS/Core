<?php

if (!class_exists(\Symfony\Component\Console\Application::class)) {
    $autoloadFileCandidates = [
        __DIR__ . '/../../../vendor/autoload.php',
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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->files()->name('*.php')->in(__DIR__ . '/../cli/Command')->depth(0);

$cliApp = new Application('prom-cms');

$ns = '\\PromCMS\\Cli\\Command\\';

foreach ($finder as $file) {
    $r = new \ReflectionClass($ns . $file->getBasename('.php'));
    if ($r->isSubclassOf(Command::class) && !$r->isAbstract()) {
        $cliApp->add($r->newInstance(null));
    }
}

$cliApp->run();
