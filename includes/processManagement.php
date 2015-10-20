<?php
  class ProcessManagement {
    private static $processes = array();

    public static function newProcess($process) {
      // Verify that the process parameter is in fact a Process class
      if (is_object($process) && get_class($process) == "Process") {
        // Store the process
        self::$processes[] = $process;
        Logger::debug("Process '".$process->getPath()."' added to the process ".
          "manager.");
        return true;
      }
      return false;
    }

    public static function getProcessByIndex($i) {
      // Check to see if a process exists at this index
      if (isset(self::$processes[$i])) {
        return self::$processes[$i];
      }
      return false;
    }

    public static function delProcessByIndex($i) {
      // Check to see if a process exists at this index
      if (isset(self::$processes[$i])) {
        // Store process in a local variable for access after it is removed
        $p = self::$processes[$i];
        // Remove the process
        Logger::debug("Process '".$p->getPath()."' removed from the process ".
          "manager.");
        unset(self::$processes[$i]);
        return true;
      }
      return false;
    }

    public static function getProcesses() {
      return self::$processes;
    }

    public static function pruneProcesses() {
      foreach (self::$processes as $key => $process) {
        if (!$process->check()) {
          Logger::debug("Pruning process '".$process->getPath().".'");
          unset(self::$processes[$key]);
        }
      }
    }
  }
?>
