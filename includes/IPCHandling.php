<?php
  class IPCHandling {
    private static $threads = array();

    public static function dispatch($module, $method, $callback, $data = null) {
      // Create a UUID for this thread
      $uuid = md5(rand().time());
      // Add the callback information to the threads property
      self::$threads[$uuid] = array($module, $callback);
      // Attempt to fork
      $pid = pcntl_fork();
      if ($pid == -1) {
        Logger::info("Couldn't fork. Exiting...");
        die();
      }
      elseif ($pid) {
        // We're in the parent; log success and add the child PID to thread info
        self::$threads[$uuid][2] = $pid;
        Logger::debug("Dispatched thread for UUID ".$uuid);
        // Attempt to wait on the child
        pcntl_wait($null, WNOHANG);
        // Return the UUID of the thread
        return $uuid;
      }
      else {
        // We're in the child
        foreach (SocketManagement::getSockets() as $socket) {
          if ($socket->getIPC() == true) {
            // Make a connection to the IPC socket
            $connection = new Connection("0", array(
              $socket->getHost(),
              $socket->getPort(),
              $socket->getSSL(),
              array()
            ), true);
            // Connect to the IPC socket
            $connection->connect();
            // Run the specified method and send its output to the IPC socket
            $connection->send(json_encode(array(
              $uuid,
              $module->$method($data)
            )));
            // Disconnect from the IPC socket
            $connection->disconnect();
            Logger::debug("Finished dispatch for UUID ".$uuid);
            break;
          }
        }
        // Make sure the child dies after it's done processing data
        die();
      }
    }

    public static function receiveData($connection, $data) {
      // Log the data received on the IPC socket
      Logger::devel(var_export($data, true));
      // Attempt to decode the received data
      $data = @json_decode($data, true);
      if (is_array($data) && isset(self::$threads[$data[0]])) {
        Logger::debug("Calling dispatch callback for UUID ".$data[0]);
        $module = self::$threads[$data[0]][0];
        $callback = self::$threads[$data[0]][1];
        // Call the provided callback for this thread
        Logger::stack("Entering module: ".$module->name."::".$callback);
        $module->$callback($data[0], $data[1]);
        Logger::stack("Left module: ".$module->name."::".$callback);
        // Attempt to wait on the child
        pcntl_wait($null, WNOHANG);
        // Remove this thread from the threads property
        unset(self::$threads[$data[0]]);
      }
      // Disconnect the thread
      $connection->disconnect();
    }
  }
?>
