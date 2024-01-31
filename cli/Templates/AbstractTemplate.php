<?php

namespace PromCMS\Cli\Templates;

use PhpParser\ParserFactory;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractTemplate
{
  protected array $ast = [];
  protected Filesystem $fs;
  protected readonly TemplateHeader $header;
  public function __construct(
    private string $target,
    protected bool $modifiable = false
  ) {
    $this->fs = new Filesystem();
    $this->header = new TemplateHeader();

    if ($modifiable) {
      $this->loadExisting();
    }
  }

  public function setTarget(string $target)
  {
    $this->target = $target;
    return $this;
  }

  public function getTarget()
  {
    return $this->target;
  }

  /**
   * Loads existing file contents and it's ast
   */
  protected function loadExisting()
  {
    if ($this->fs->exists($this->target)) {
      $parser = (new ParserFactory())->createForNewestSupportedVersion();
      $fileContents = file_get_contents($this->target);

      if ($fileContents === false) {
        throw new \Exception("Could not read existing file $this->target");
      }

      try {
        $ast = $parser->parse($fileContents);

        if (!empty($ast)) {
          $this->ast = $ast;
        }
      } catch (\Exception $error) {
        echo "Could not parse existing file: {$error->getMessage()}\n";
        return;
      }
    }
  }

  public function save()
  {
    $prettyPrinter = new TemplatePrinter;
    $targetDirname = dirname($this->target);
    if (!$this->fs->exists($targetDirname)) {
      $this->fs->mkdir($targetDirname);
    }

    return file_put_contents($this->target, $prettyPrinter->prettyPrintFile($this->ast));
  }
}