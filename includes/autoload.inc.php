<?php

spl_autoload_register(
  function ($class_name) {
    $class_parts = explode('\\', $class_name);
    array_shift($class_parts);
    $path = '/classes/' . implode('/', $class_parts) . '.class.php';
    include_once(__DIR__ . $path);
  }
);