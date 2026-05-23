<?php

namespace SlyDevil\Site;

class Database {

  protected ?\mysqli $db = NULL;

  public function __construct() {
    $this->connect();
  }

  public function connect() {
    if (!$this->db) {
      $this->db = mysqli_init();
      mysqli_real_connect($this->db, getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'), getenv('DB_PORT') ?? 3306, NULL, MYSQLI_CLIENT_SSL);

      if ($this->db->connect_errno > 0) {
        die('Connect Error (' . $this->db->connect_errno . ') ' . $this->db->connect_error);
      }

      $this->set_time_zone();
      $this->db->query("SET character_set_database = 'utf8'");
    }
  }

  public function disconnect() {
    $this->db->close();
  }

  public function error() {
    return $this->db->error;
  }

  public function escape(string $value) {
    $this->connect();

    return $this->db->real_escape_string($value);
  }

  public function insert_id() {
    $this->connect();

    return $this->db->insert_id;
  }

  public function query(string $sql, array $args = []) {
    $this->connect();

    $sql_args = [];
    foreach ($args as $arg) {
      $sql_args[] = $this->escape($arg);
    }

    return $this->db->query(vsprintf($sql, $sql_args));
  }

  public function set_time_zone() {
    $this->connect();

    $dtz  = new \DateTimeZone(date_default_timezone_get());
    $time = new \DateTime("now", $dtz);
    $offset = $dtz->getOffset($time) / 3600;

    $this->db->query("SET SESSION time_zone = '" . $offset . ":00'");
  }

}
