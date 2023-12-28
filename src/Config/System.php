<?php
namespace PromCMS\Core\Config;

final class SystemModules extends ConfigBase
{
  public string $modelsFolderName;
  public string $controllersFolderName;
}

class SystemLogging extends ConfigBase
{
  public string|null $logFilepath = null;
}

final class System extends ConfigBase
{
  public SystemModules $modules;
  public SystemLogging $logging;
}