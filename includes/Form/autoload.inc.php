<?php

spl_autoload_register(
  function ($class_name) {
    if (str_starts_with($class_name, 'SlyDevil\Form')) {
      $class_parts = explode('\\', $class_name);
      $class_parts = array_slice($class_parts, 2);
      $path = '/src/' . implode('/', $class_parts) . '.php';
      include_once(__DIR__ . $path);
    }
  }
);