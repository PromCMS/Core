<?php
namespace PromCMS\Core\Config;

class SecuritySession extends ConfigBase {
  public int $lifetime;
}

class SecurityToken extends ConfigBase {
  public int $lifetime; 
}

class Security extends ConfigBase {
  public SecuritySession $session;
  public SecurityToken $token;
}