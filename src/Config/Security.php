<?php
namespace PromCMS\Core\Config;

class SecuritySession extends ConfigBase
{
  /**
   * Session lifetime. Anything that strtotime() accepts is valid
   */
  public string $lifetime = "1 hour";

  public string $name = "prom_session";
}

class SecurityToken extends ConfigBase
{
  public int $lifetime = 86400;
}

class Security extends ConfigBase
{
  public SecuritySession $session;
  public SecurityToken $token;
}