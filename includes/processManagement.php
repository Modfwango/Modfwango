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

    // public static function getProcessesWithData() {
    //   // Define a default return value
    //   $result = array();
    //
    //   // Define storage for the resources
    //   $reade  = array();
    //   $reado  = array();
    //   $write  = null;
    //   $except = null;
    //
    //   // Define storage for the results from socket_select(...)
    //   $rese   = array();
    //   $reso   = array();
    //
    //   // Populate the arrays used by socket_select(...)
    //   foreach (self::getProcesses() as $index => $process) {
    //     // Assign mutable array variables
    //     $rese[] = $process->getSTDERR();
    //     $reso[] = $process->getSTDOUT();
    //     // Assign housekeeping array variables
    //     $reade[@intval($process->getSTDERR())] = $index;
    //     $reado[@intval($process->getSTDOUT())] = $index;
    //   }
    //
    //   // Perform the socket_select(...) calls
    //   $statuse = @socket_select($rese, $write, $except, 0);
    //   $statuso = @socket_select($reso, $write, $except, 0);
    //   $status  = $statuse || $statuso;
    //
    //   // Check if there are any sockets with waiting buffers
    //   if ($status) {
    //     Logger::debug("Process has data available.");
    //     foreach (array_merge($rese, $reso) as $resource) {
    //       // Add any existent processes to the result array
    //       if (isset($reade[@intval($resource)]))
    //         $result[] = self::$processes[$reade[@intval($resource)]];
    //       if (isset($reado[@intval($resource)]))
    //         $result[] = self::$processes[$reado[@intval($resource)]];
    //     }
    //   }
    //
    //   // Return any processes with waiting buffers
    //   return $result;
    // }

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
