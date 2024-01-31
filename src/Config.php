<?php
namespace PromCMS\Core;

use PromCMS\Core\Internal\Config\Security as ConfigPart__Security;
use PromCMS\Core\Internal\Config\Environment as ConfigPart__Environment;
use PromCMS\Core\Internal\Config\System as ConfigPart__System;
use PromCMS\Core\Internal\Config\ConfigBase;

class Config extends ConfigBase
{
  public ConfigPart__Security $security;
  public ConfigPart__Environment $env;
  public ConfigPart__System $system;
}