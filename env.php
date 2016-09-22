<?php
  class Env {
    public static function home() {
      return trim(`echo ~`, "\n");;
    }

    public static function pathSearch($fn) {
      $path = `echo \$PATH`;
      $path = explode(":", $path);
      foreach ($path as $place) {
        if (file_exists($place . "/" . $fn)) {
          return $place;
        }
      }
      return false;
    }

    public static function getAbsolute($fn) {
      $p = self::pathSearch($fn);
      return $p . "/" . $fn;
    }

  }
?>
