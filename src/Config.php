<?php
namespace PromCMS\Core;

use PromCMS\Core\Config\Security as ConfigPart__Security;
use PromCMS\Core\Config\Environment as ConfigPart__Environment;
use PromCMS\Core\Config\Filesystem as ConfigPart__Filesystem;
use PromCMS\Core\Config\i18n as ConfigPart__i18n;
use PromCMS\Core\Config\System as ConfigPart__System;
use PromCMS\Core\Config\ConfigBase;

class Config extends ConfigBase
{
  public ConfigPart__Security $security;
  public ConfigPart__Environment $env;
  public ConfigPart__Filesystem $fs;
  public ConfigPart__i18n $i18n;
  public ConfigPart__System $system;
}