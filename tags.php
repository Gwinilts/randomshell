<?php
  class TAGS {
    private static $con = NULL;

    public static function get() {
      if (NULL !== self::$con) return self::$con;

      try {

        return (self::$con = new PDO('mysql:host=localhost;dbname=random;charset=utf8mb4', "random", "modnar"));

      } catch (PDOException $e) {
        return false;
      }
    }

    public static function addTag($a, $b) {
      $pdo = self::get();
      $st = $pdo->prepare("INSERT INTO tags (path, tag) VALUES (:a, :b)");
      $st->bindValue(":a", $a);
      $st->bindValue(":b", $b);
      $st->execute();
    }

    public static function debug() { // never ever call this function
      $pdo = self::get();
      $st = $pdo->prepare("SELECT * FROM tags");
      $st->execute();

      $st = $st->fetchAll();

      foreach ($st as $row) {
        var_dump($row);
      }
    }
  }
?>
