<?php

namespace PromCMS\Cli;
use Symfony\Component\Filesystem\Filesystem;

class Template
{
  private Filesystem $fs;

  public function __construct(private string $filePath) {
    $this->fs = new Filesystem();
  }

  public static function create(string $filePath) {
    return new self($filePath);
  }

  public function render(array $vars = []): string
  {
    extract($vars);
    ob_start();

    ob_implicit_flush(false);

    try {
      require $this->filePath;
    } catch (\Exception $e) {
      ob_end_clean();

      throw $e;
    }

    return (string) ob_get_clean();
  }

  public function renderTo(string $to, array $vars = []) {
    $content = $this->render($vars);

    $this->fs->dumpFile($to, $content);
  }
}