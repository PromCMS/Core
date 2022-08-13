<?php
namespace PromCMS\Core\Config;

class SystemModules extends ConfigBase {
  public string $modelsFolderName;
  public string $controllersFolderName;
}

class System extends ConfigBase {
  public SystemModules $modules;
}