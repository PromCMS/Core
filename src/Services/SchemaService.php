<?php

namespace PromCMS\Core\Services;
use DI\Container;
use PromCMS\Core\Schema;
use PromCMS\Core\Utils\FsUtils;

class SchemaService {

  public function __construct(Container $container) {
  }

  function createFromPath (string $path) {
    $fileContents = FsUtils::readFile($path);

    if (!$fileContents || json_decode($fileContents) === null) {
      throw new \Exception("Could not read contents from path '$path'");
    }

    return new Schema((object) json_decode($fileContents));
  }

  /**
   * @param \object|string $input
   */
  function createSchema(object $input) {
    return new Schema($input);
  }
}