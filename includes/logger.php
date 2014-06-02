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
  }
?>
