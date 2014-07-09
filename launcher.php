<?php
  if (version_compare(phpversion(), '5.1.1', '<')) {
      die("You must have PHP version 5.1.1 or higher to use Modfwango.\n");
  }

  define("__TIMEZONE__", "America/Chicago");
  define("__PROJECTROOT__", dirname(__FILE__));

  if (basename(__FILE__) != "main.php") {
    rename(__FILE__, __PROJECTROOT__."/main.php");
  }

  $missing = array();
  $directories = array(
    __PROJECTROOT__."/conf",
    __PROJECTROOT__."/conf/connections",
    __PROJECTROOT__."/data",
    __PROJECTROOT__."/modules"
  );
  $files = array(
    __PROJECTROOT__."/conf/modules.conf",
    __PROJECTROOT__."/conf/listen.conf",
  );
  foreach ($directories as $directory) {
    if (!file_exists($directory)) {
      mkdir($directory);
    }
  }
  foreach ($files as $file) {
    if (!file_exists($file)) {
      $missing[] = $file;
      touch($file);
    }
  }

  $ending = "\n * ";
  if (count($missing) > 0) {
    die("Some mandatory configuration files were missing, and thus replaced.  ".
      "They are listed below:".$ending.implode($ending, $missing)."\n");
  }

  require_once(__PROJECTROOT__."/.modfwango/main.php");
?>
