<?php

namespace SlyDevil;

use SlyDevil\Env;

class Database {
    
  protected static ?\mysqli $db = NULL;

  public function __construct() {    
    self::connect();
  }

  public static function connect() {
    if (!self::$db) {
      self::$db = new \mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
          
      if (self::$db->connect_errno > 0) {
        die("Connect Error (" . self::$db->connect_errno . ") " . self::$db->connect_error);
      }

      self::set_time_zone();
      self::$db->query("SET character_set_database = 'utf8'");
    }
  }
    
  public static function disconnect() {
    self::$db->close();
  }
    
  public static function error() {
    return self::$db->error;
  }
    
  public static function escape(string $value) {
    self::connect();
       
    return self::$db->real_escape_string(Env::filterVariable($value));
  }
    
  public static function insert_id() {
    self::connect();
        
    return self::$db->insert_id;
  }
    
  public function query(string $sql, array $args = []) {
    self::connect();

    $sql_args = [];
    foreach ($args as $arg) {
      $sql_args[] = self::escape($arg);
    }

    return self::$db->query(vsprintf($sql, $sql_args));
  }
    
  public static function set_time_zone() {
    self::connect();

    $dtz  = new \DateTimeZone(date_default_timezone_get());
    $time = new \DateTime("now", $dtz);
    $offset = $dtz->getOffset($time) / 3600;

    self::$db->query("SET SESSION time_zone = '" . $offset . ":00'");
  }

}
