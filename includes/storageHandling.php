<?php
  class StorageHandling {
    public static function createDirectory($module, $name) {
      // Setup a string of the path of the directory that should be created.
      $mname = $module->name;
      $file = __PROJECTROOT__."/data/".$mname."/".$name;

      Logger::debug("Preparing to create directory at ".$file);
      // Make sure all other directories, etc, are initialized.
      if (self::initDirectories($mname)) {
        Logger::debug("Directories are initialized.");
        // Make sure the module isn't trying to write outside of its sandbox.
        if (substr(realpath(dirname($file)), 0, strlen(__PROJECTROOT__))
            == __PROJECTROOT__) {
          Logger::debug("Sandbox test passed.  Continuing check.");
          // Make sure the parent directory is writable and the target doesn't
          // exist.
          if (file_exists(dirname($file)) && is_dir(dirname($file))
              && is_writable(dirname($file)) && !file_exists($file)) {
            // Create the requested directory.
            Logger::debug("Creating directory now.");
            return mkdir($file);
          }
        }
        else {
          // A module tried writing outside of its sandbox.  This might be bad.
          Logger::info("Module ".$module->name.
            " tried writing outside of its sandbox.");
          Logger::debug("Tried writing to ".realpath(dirname($file)));
          Logger::debug(substr(realpath(dirname($file)), 0,
            strlen(__PROJECTROOT__))." != ".__PROJECTROOT__);
        }
      }
      return false;
    }

    public static function loadFile($module, $name) {
      // Setup a string of the path of the file that should be loaded.
      $mname = $module->name;
      $file = __PROJECTROOT__."/data/".$mname."/".$name;

      Logger::debug("Preparing to load file at ".$file);
      // Make sure all other directories, etc, are initialized.
      if (self::initDirectories($mname, $name)) {
        Logger::debug("Directories are initialized.");
        // Make sure the module isn't trying to write outside of its sandbox.
        if (substr(realpath($file), 0, strlen(__PROJECTROOT__))
            == __PROJECTROOT__) {
          Logger::debug("Sandbox test passed.  Continuing check.");
          // Make sure the file is readable.
          if (is_readable($file)) {
            Logger::debug("Reading file now.");
            // Return the contents of the file.
            return file_get_contents($file);
          }
        }
        else {
          // A module tried writing outside of its sandbox.  This might be bad.
          Logger::info("Module ".$module->name.
            " tried reading outside of its sandbox.");
          Logger::debug("Tried reading from ".realpath($file));
          Logger::debug(substr(realpath($file), 0, strlen(__PROJECTROOT__)).
            " != ".__PROJECTROOT__);
        }
      }
      return false;
    }

    public static function saveFile($module, $name, $contents,
        $append = false) {
      // Setup a string of the path of the file that should be loaded.
      $mname = $module->name;
      $file = __PROJECTROOT__."/data/".$mname."/".$name;

      Logger::debug("Preparing to write to file at ".$file);
      // Make sure all other directories, etc, are initialized.
      if (self::initDirectories($mname, $name)) {
        Logger::debug("Directories are initialized.");
        // Make sure the module isn't trying to write outside of its sandbox.
        if (substr(realpath($file), 0, strlen(__PROJECTROOT__))
            == __PROJECTROOT__) {
          Logger::debug("Sandbox test passed.  Continuing check.");
          // Make sure the file is writable.
          if (is_writable($file)) {
            Logger::debug("Writing to file now.");
            // Write the contents of the file.
            if ($append == true) {
              return file_put_contents($file, $contents, FILE_APPEND);
            }
            return file_put_contents($file, $contents);
          }
        }
        else {
          // A module tried writing outside of its sandbox.  This might be bad.
          Logger::info("Module ".$module->name.
            " tried writing outside of its sandbox.");
          Logger::debug("Tried writing to ".realpath($file));
          Logger::debug(substr(realpath($file), 0, strlen(__PROJECTROOT__)).
            " != ".__PROJECTROOT__);
        }
      }
      Logger::debug("Write failed.");
      return false;
    }

    private static function initDirectories($mname, $name = null) {
      // Setup strings of paths to create.
      $data = __PROJECTROOT__."/data";
      $moddir = $data."/".$mname;
      $file = $moddir."/".$name;

      // Attempt to create the data directory if it doesn't exist already.
      if (!file_exists($data)) {
        $ret = mkdir($data);
        if ($ret == false) {
          Logger::debug("Could not create folder at ".$data);
          return false;
        }
      }

      // Attempt to create the module directory if it doesn't exist already.
      if (!file_exists($moddir)) {
        $ret = mkdir($moddir);
        if ($ret == false) {
          Logger::debug("Could not create folder at ".$moddir);
          return false;
        }
      }

      // Attempt to create the file if it doesn't exist already.
      if ($name != null && !file_exists($file)) {
        $ret = touch($file);
        if ($ret == false) {
          Logger::debug("Could not create file at ".$file);
          return false;
        }
      }

      return true;
    }
  }
?>
