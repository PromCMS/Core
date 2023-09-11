<?php 

namespace PromCMS\Core;

// TODO: in feature we want every instance of each loaded module accessible on app instance
class Module {
  static string $modulesRoot = '';
  static $moduleInfoFileName  = 'module-info.json';
  static $frontRoutesFileName  = 'front.routes.php';
  static $apiRoutesFileName  = 'api.routes.php';
  static $viewsFolderName  = 'Views';
  static $bootstrapFileName  = 'bootstrap.php';
  static $afterBootstrapFileName  = 'bootstrap.after.php';
  
  private $config;
  private $path;

  function __construct($moduleRootPath) {
    $modulePath = $moduleRootPath;

    if (str_contains($modulePath, '@modules:')) {
      $modulePath = Path::join(static::$modulesRoot, str_replace('@modules:', '', $modulePath));
    }

    $moduleInfoPath = Path::join($modulePath, static::$moduleInfoFileName);

    if (!file_exists($moduleInfoPath)) {
      throw new \Exception("Module must have ".static::$moduleInfoFileName." file created");
    }

    $this->config = (array) json_decode(file_get_contents($moduleInfoPath));
    $this->path = $modulePath;

    if (!$this->config) {
      throw new \Exception("Not a valid module info in ".static::$moduleInfoFileName);
    }

    if (!isset($this->config["name"])) {
      throw new \Exception("Please define your module name in ".static::$moduleInfoFileName);
    }
  }

  // TODO: Is this really necessary? Should we honour name field instead?
  public function getFolderName() {
    return basename($this->getPath());
  }

  public function getName() {
    return $this->config["name"];
  }

  /**
   * Returns order field of current module. If not defined then returns null
   * 
   * @return number|null
   */
  public function getOrder() {
    return isset($this->config["order"]) ? $this->config["order"] : null;
  }

  /**
   * Returns path to the root of module
   * 
   * @return string
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * Returns is current module is enabled, defaults to true
   * 
   * @return boolean
   */
  public function isEnabled() {
    return isset($this->config["enabled"]) ? $this->config["enabled"] : true;
  }
}