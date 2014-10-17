<?php
  class Logger {
    public static function debug($msg) {
      // If debug mode is on, show debug messages
      if (__LOGLEVEL__ > 2) {
        // Split multi-line messages
        if (stristr($msg, "\n")) {
          foreach (explode("\n", trim($msg)) as $line) {
            self::displayDebug($line);
          }
          return;
        }
        // Show a message
        self::displayDebug(trim($msg));
      }
    }

    public static function devel($msg) {
      // If debug mode is on, show debug messages
      if (__LOGLEVEL__ > 3) {
        // Split multi-line messages
        if (stristr($msg, "\n")) {
          foreach (explode("\n", trim($msg)) as $line) {
            self::displayDevel($line);
          }
          return;
        }
        // Show a message
        self::displayDevel(trim($msg));
      }
    }

    private static function displayDebug($msg) {
      echo " [ DEBUG ] ".$msg."\n";
    }

    private static function displayDevel($msg) {
      echo " [ DEVEL ] ".$msg."\n";
    }

    private static function displayInfo($msg) {
      echo "  [ INFO ] ".$msg."\n";
    }

    private static function displayStack($msg) {
      echo " [ STACK ] ".$msg."\n";
    }

    public static function info($msg) {
      // Split multi-line messages
      if (stristr($msg, "\n")) {
        foreach (explode("\n", trim($msg)) as $line) {
          self::displayInfo($line);
        }
        return;
      }
      // Show a message
      self::displayInfo(trim($msg));
    }

    public static function getMemoryUsage() {
      return "Memory Usage:  ".
        self::prepareNumber(intval(memory_get_usage() / 1024 / 1024)). " MB (".
        self::prepareNumber(intval(memory_get_usage() / 1024)). " KB)";
    }

    public static function printMemoryUsage() {
      self::info(self::getMemoryUsage());
    }

    private static function prepareNumber($num) {
      $num = strval($num);
      if (strlen($num) < 4) {
        return $num;
      }
      $commacount = intval(strlen($num) / 3);
      $number = array();
      $i = 1;
      while ($i <= $commacount) {
        $number[] = substr($num, -($i * 3), 3);
        $i++;
      }

      $remaining = null;
      if (strlen($num) > (count($number) * 3)) {
        $remaining = substr($num, 0, (strlen($num) - (count($number) * 3))).",";
      }
      return $remaining.implode(",", array_reverse($number));
    }

    public static function stack($msg) {
      // If stack mode is on, show stack messages
      if (__LOGLEVEL__ > 1) {
        // Split multi-line messages
        if (stristr($msg, "\n")) {
          foreach (explode("\n", trim($msg)) as $line) {
            self::displayStack($line);
          }
          return;
        }
        // Show a message
        self::displayStack(trim($msg));
      }
    }
  }
?>
