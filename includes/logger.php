<?php
  class Logger {
    public static function debug($msg) {
      // If debug mode is on, show debug messages.
      if (__DEBUG__ == true) {
        // Show a message.
        echo " [ DEBUG ] ".trim($msg)."\n";
      }
    }

    public static function info($msg) {
      // Show a message.
      echo " [ INFO ]  ".trim($msg)."\n";
    }

    public static function memoryUsage() {
      self::info("Memory Usage:  ".self::prepareNumber(intval(
        memory_get_usage() / 1024 / 1024)). " MB (". self::prepareNumber(intval(
        memory_get_usage() / 1024)). " KB)");
    }

    private static function prepareNumber($num) {
      $num = strval($num);
      if ($num < 4) {
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
  }
?>
