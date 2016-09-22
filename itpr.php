<?php
/**
ITPR Version 0.02

Changelog:
0.01:
First draft. ITPR is an interpreter.
It has two methods registerVerb and think.

registerVerb associates a word (verb) with a callback

think looks at the first word of (deliniated by a space) of it's argument
for an associated callback.
If it finds one, it calls it with the rest the argument (anything after the first space) as it's
argument.
0.02:
Added 'Aliases' which serve the same function
as set does in bash.
Basically, you register a text alias and instances of it
will be replaced with the alias target if they should pass through think

The syntax is almost the same as in bash.
**/

  class ITPR {
    private static $verbs = NULL;

    public static $alias = NULL;

    public static function verbDigest() {
      IO::outLn("I know about: ");
      foreach (self::$verbs as $key => $nul) {
        IO::out($key . " ");
      }
      IO::outLn();
    }

    public static function registerVerb($verb, $function=NULL)  {
      if ($function == NULL) $function = $verb;

      if (self::$verbs == NULL) {
        self::$verbs = array();
      }

      self::$verbs{strtoupper($verb)} = $function;
    }

    public static function textAlias($arg) {
      if (strlen(trim($arg)) < 1) {
        foreach (self::$alias as $key => $val) {
          IO::outLn($key . "=" . "$val");
        }
        return;
      }
      $a = explode("=", $arg);

      /**
        THIS IS A HACK
      **/
      if ($a{0} == "PS1") {
        IO::setPrompt($a{1});
      }
      /**
        FIX IT PLZ
      **/



      self::$alias{"$" . trim($a{0})} = trim($a{1});
    }

    public static function think($s) {
      if (self::$alias == NULL) {
        self::$alias = array("~" => trim(`echo ~`, "\n"));
        self::registerVerb("alias", "ITPR::textAlias");
      }

      if (self::$verbs == NULL) return;

      $sentence = preg_replace('!\s+!', ' ', $s);

      $wordList = explode(" ", $sentence);
      $wordList{0} = strtoupper($wordList{0});

      if (@self::$verbs{$wordList{0}} == NULL) {
        return false;
      } else {
        $rem = substr($sentence, strlen($wordList{0}));
        foreach (self::$alias as $key => $val) {
          if (strpos($rem, $key) !== FALSE) {
            $rem = str_replace($key, $val, $rem);
          }
        }
        
        self::$verbs{$wordList{0}}(trim($rem));
        return true;
      }
    }
  }


?>
