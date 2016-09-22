<?php
/**
  This is still a very hackey class
  IO Version 0.02

  The Purpose of this class is to simulate terminal behaiviour.
  It is different from a typical IO::in IO::out setup
  as the in method filters control sequences that can alter
  the 'percieved' behaiviour of the input.

  In other words, pressing up will take you back through the
  command history, pressing left will allow you to edit the current command etc.

  As i've said it's very hackey, so beware when maintaining.
**/
class IO {
  private static $buffer = "";
  private static $cseq = array();
  private static $scseq = array();
  private static $stdin = NULL;
  private static $stdout = NULL;
  private static $prompt = "";
  private static $history = array();
  private static $historyCursor = -1;
  private static $dhistory = "";
  private static $cpos = 0;

  private static function buffer_set($str=NULL, $c=false) {
    if ($str == NULL) $str = self::$buffer;
    self::out("\r\e[K\r[" . self::$prompt . "] :> " . $str);
    self::$buffer = $str;
    if ($c) {
      self::$cpos = strlen($str);
    }
  }

  public static function history_prev() {
    // what do we do?
    if (self::$historyCursor < 0) {
      self::$historyCursor = sizeof(self::$history) - 1;
      self::$dhistory = self::$buffer;
    } else {
      self::$historyCursor--;
    }

    if (self::$historyCursor > -1) {
      self::buffer_set(self::$history{self::$historyCursor}, true);
    } else {
      self::buffer_set("", true);
    }
  }

  public static function history_next() {
    // what do we do?

    if (self::$historyCursor < sizeof(self::$history)) {
      self::$historyCursor++;
    }

    if (self::$historyCursor < sizeof(self::$history)) {
      self::buffer_set(self::$history{self::$historyCursor}, true);
    } else {
      self::buffer_set(self::$dhistory, true);
      self::$dhistory = "";
    }
  }

  public static function cursor_left() {
    self::$cpos -= 1;
    if (self::$cpos < 0) self::$cpos = 0;
    self::buffer_set();
    self::curs();
  }

  public static function cursor_right() {
    self::$cpos += 1;
    if (self::$cpos > strlen(self::$buffer)) self::$cpos = strlen(self::$buffer);
    self::buffer_set();
    self::curs();
  }

  public static function del() {
    die("del");
  }

  public static function curs() {
    $npos = strlen(self::$buffer) - self::$cpos;
    if (self::$cpos == strlen(self::$buffer)) {
      self::buffer_set();
    } else {
      self::out("\e[" . $npos . "D");
    }
  }

  public static function reg_ctrl_seq($seq, $f) {
    self::$cseq{$seq} = $f;
    if (strlen($f) == 1) {
      array_push(self::$scseq, $f);
    }
  }

  private static function in() {
    if (self::$stdin == NULL) {
      self::$stdin = fopen("/dev/stdin", 'r');
      `stty -icanon`;
      self::reg_ctrl_seq("\e[A", "IO::history_prev");
      self::reg_ctrl_seq("\e[B", "IO::history_next");
      self::reg_ctrl_seq("\e[D", "IO::cursor_left");
      self::reg_ctrl_seq("\e[C", "IO::cursor_right");
      self::reg_ctrl_seq("\^H", "IO::del");
    }

    while (strlen($c = fgetc(self::$stdin)) < 1) {

    }

    $ctl = $c == "\e";

    foreach (self::$scseq as $cs) {
      $ctl |= $cs == $c;
    }

    if ($ctl) {
      $cs = "\e";

      while (@self::$cseq{$cs} == NULL) {
        $cs .= fgetc(self::$stdin);
      }

      self::buffer_set(self::$buffer);

      if (@self::$cseq{$cs} !== NULL) {
        self::$cseq{$cs}();
      }

    } else {
      if (strlen(self::$buffer) !== self::$cpos) {
        self::$buffer = substr(self::$buffer, 0, self::$cpos) . $c . substr(self::$buffer, self::$cpos);
        self::$cpos++;
        self::buffer_set();
        self::curs();

      } else {
        self::$buffer .= $c;
        self::$cpos++;
        self::buffer_set(NULL,true);
      }
    }
  }

  public static function inLn() {
    self::in();

    if (strpos(self::$buffer, "\n") !== FALSE) {
      self::$buffer = str_replace("\n", "", self::$buffer);
      $a = self::$buffer;
      self::$buffer = "";
      array_push(self::$history, $a);
      self::$historyCursor = -1;
      self::$cpos = 0;
      return $a;
    } else {
      return self::inLn();
    }
  }

  public static function out($msg) {
    if (self::$stdout == NULL) {
      self::$stdout = fopen("/dev/stdout", 'a');
    }
    fwrite(self::$stdout, $msg);
  }

  public static function outLn($msg="") {
    self::out($msg . "\n");
  }

  public static function prompt($msg) {
    self::out("[" . trim($msg) . "] :> ");
    return self::inLn();
  }

  public static function aprompt() {
    return self::prompt(self::$prompt);
  }

  /**
  If you read through itpr.php you'll see
  the words "THIS IS A HACK"

  They're refferring to the fact that the setPrompt method
  is part of the IO class instead of the itpr class
  which is a bit of bad planning on my part...

  In the future, all itpr delegations should happen
  in itpr and not here. This includes displaying the prompt

  Since itpr is the only class that will ever display a prompt anyway,
  setPrompt, prompt and aprompt should all be members of itpr and not IO
  **/
  public static function setPrompt($msg) {
    self::$prompt = $msg;
  }
}
?>
