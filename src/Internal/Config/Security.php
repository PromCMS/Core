<?php
namespace PromCMS\Core\Internal\Config;

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
final class SecuritySession extends ConfigBase
{
  /**
   * Session lifetime. Anything that strtotime() accepts is valid
   */
  public string $lifetime = "1 hour";

  public string $name = "prom_session";
}

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
final class SecurityToken extends ConfigBase
{
  public int $lifetime = 86400;
}

/**
 * @internal Part of PromCMS Core and should not be used outside of it
 */
final class Security extends ConfigBase
{
  public SecuritySession $session;
  public SecurityToken $token;
}