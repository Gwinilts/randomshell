<?php
  class File {

    private static $mimes = NULL;

    private static function popMimes() {
      if (file_exists(Env::home() . "/.randomrc")) {
        $mf = fopen(Env::home() . "/.randomrc", "r");

        $mimes = array();

        while (($line = fgets($mf)) !== FALSE) {
          $mime = explode(":", $line);
          self::$mimes{$mime{0}} = $mime{1};
        }

        fclose($mf);
      } else {
        self::$mimes = array();
      }
    }

    public static function addMime($m, $p) {
      if (Env::pathSearch($p) !== FALSE) {
        $p = Env::getAbsolute($p);
      }
      if (file_exists($p)) {
        self::$mimes{$m} = $p;
        $mf = fopen(Env::home() . "/.randomrc", "a");

        foreach (self::$mimes as $key => $val) {
          fwrite($mf, "$key:$val");
        }
        fclose($mf);
        IO::outLn("Ok.");
      } else {
        IO::outLn("I won't associate '$m' with '$p' because '$p' doesn't exist.");
      }
    }

    public static function open($path) {
      if (self::$mimes == NULL) self::popMimes();

      if (file_exists($path)) {
        if (@self::$mimes{$m = mime_content_type($path)} == NULL) {
          IO::outLn("I don't know what to do with '" . $m . "'s");
        } else {
          $prog = self::$mimes{mime_content_type($path)};
          IO::outLn("Opening " . $path . " with " . $prog);
          `exec $prog $path &> /dev/null &`;
          IO::outLn("Ok.");
        }
      } else {
        IO::outLn("'$path' doesn't exist!");
      }
    }


  }
?>
