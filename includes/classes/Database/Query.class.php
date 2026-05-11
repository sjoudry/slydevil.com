<?php

namespace SlyDevil\Database;

class Query {

  protected \mysqli_result $result;

  public static function create(string $sql, array $args = []) {
    return new static($sql, $args);
  }

  public function __construct(string $sql, $args = []) {
    $db = new Connection();

    $sql_args = [];
    foreach ($args as $arg) {
      $sql_args[] = $db->escape($arg);
    }

    $this->result = $db->query(vsprintf($sql, $sql_args));

    return $this;
  }
    
  public function result() {
    return $this->result;
  }
 
}