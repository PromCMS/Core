<?php

namespace PromCMS\Core;

class Schema {

  
  function create() {

  }


  /**
   * @param string $fileLocation Location to the json file. 
   *                             This can be relative or absolute file path to filesystem, or location on the internet. 
   *                             This can also be a path with @modules:<module name> prefix to resolve into module right away
   */
  function load (string $fileLocation) {
    $json = "";

    if (str_starts_with($fileLocation, "@modules:")) {
      
    }

    return json_decode($json);
  }
}