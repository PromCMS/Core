<?php

namespace PromCMS\Core\Rendering\Twig\Extensions;

use DI\Container;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ArrayUtilsExtension extends AbstractExtension
{
  private Container $container;

  public function __construct(Container $container)
  {
    $this->container = $container;
  }

  public function getFilters()
  {
    return [
      new TwigFilter('pick', [$this, 'arrayPick']),
    ];
  }

  public function arrayPick($value, $keys)
  {
    if (
      !is_array($value)
    ) {
      throw new \Exception('ERROR: Value passed to "arrayPick" was not an array');
    }

    if (!is_array($keys)) {
      throw new \Exception('ERROR: Parameter passed to "arrayPick" was not an array');
    }

    return array_filter($value, function ($key) use ($keys) {
      return in_array($key, $keys);
    });
  }
}
