<?php
namespace PromCMS\Core\Config;

final class Environment extends ConfigBase
{
  public bool $development = false;
  public bool $debug = false;
  public string $env = "production";
}