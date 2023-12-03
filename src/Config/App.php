<?php
namespace PromCMS\Core\Config;

class App extends ConfigBase
{
  public string $name = 'PromCMS Project';
  public string $root;
  public string $url;
  public string $prefix = '';
  public string $baseUrl;
}