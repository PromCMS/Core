<?php
namespace PromCMS\Core\Internal\Config;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
final class Environment extends ConfigBase
{
  public bool $development = false;
  public bool $debug = false;
  public string $env = "production";
}