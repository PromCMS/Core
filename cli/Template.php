<?php

class Template
{
  protected string $filepath;

  public function render(array $vars = []): string
  {
    extract($vars);
    ob_start();

    ob_implicit_flush(false);

    try {
      if ($filepath !== null) {
        require $filepath;
      }
    } catch (Exception $e) {
      ob_end_clean();

      throw $e;
    }

    return (string) ob_get_clean();
  }
}