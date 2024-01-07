<?php
namespace PromCMS\Core\Internal\Config;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
final class SystemModules extends ConfigBase
{
  public string $modelsFolderName;
  public string $controllersFolderName;
}

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
class SystemLogging extends ConfigBase
{
  public string|null $logFilepath = null;
}

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
final class System extends ConfigBase
{
  public SystemModules $modules;
  public SystemLogging $logging;
}