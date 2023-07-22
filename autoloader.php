<?php

spl_autoload_register(function(String $class) {

  $prefix = "Resolver\\";
  $classesDir = sprintf("%s/src/", __DIR__);

  $class = str_replace("\\", "/", substr($class, strlen($prefix)));
  $classFilePath = sprintf("%s%s.php", $classesDir, $class);

  if (is_readable($classFilePath)) {
    require_once $classFilePath;
  }

});
