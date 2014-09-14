<?php
  class ConnectionManagement {
    private static $connections = array();

    public static function newConnection($connection) {
      // Verify that the connection parameter is in fact a Connection class.
      if (is_object($connection) && (get_class($connection) == "Connection"
          || get_parent_class($connection) == "Connection")
          && $connection->configured() == true) {
        // Store the connection.
        self::$connections[] = $connection;
        Logger::debug(($connection->getIPC() ? "IPC " : null)."Connection '".
          $connection->getConnectionString()."' added to the connection ".
          "manager.");
        return true;
      }
      return false;
    }

    public static function getConnectionByHost($host) {
      // See if an index for a particular connection exists with provided host.
      $i = self::getConnectionIndexByHost($host);
      if ($i !== false) {
        // Get the connection at said index and return it.
        return self::getConnectionByIndex($i);
      }
      return false;
    }

    public static function getConnectionByIndex($i) {
      // Check to see if a connection exists at this index.
      if (isset(self::$connections[$i])) {
        return self::$connections[$i];
      }
      return false;
    }

    public static function delConnectionByHost($host) {
      // See if an index for a particular connection exists with provided host.
      $i = self::getConnectionIndexByHost($host);
      if ($i !== false) {
        // Remove this connection by its index.
        return self::delConnectionByIndex($i);
      }
      return false;
    }

    public static function delConnectionByIndex($i) {
      // Check to see if a connection exists at this index.
      if (isset(self::$connections[$i])) {
        // Store connection in a local variable for access after it is removed.
        $c = self::$connections[$i];
        // Remove the connection.
        Logger::debug("Connection '".
          self::$connections[$i]->getConnectionString().
          "' removed from the connection manager.");
        unset(self::$connections[$i]);
        // Disconnect the socket after it's removed.
        $c->disconnect();
        return true;
      }
      return false;
    }

    public static function getConnectionIndexByHost($host) {
      // Iterate through each connection.
      foreach (self::$connections as $key => $connection) {
        // Check to see if the connection's host matches the provided host.
        if (strtolower(trim($connection->getHost()))
            == strtolower(trim($host))) {
          // Return this connection's index.
          return $key;
        }
      }
      return false;
    }

    public static function getConnections() {
      return self::$connections;
    }

    public static function pruneConnections() {
      foreach (self::$connections as $key => $connection) {
        if (!$connection->isAlive() && $connection->getType() == "1") {
          Logger::debug("Pruning connection '".
            $connection->getConnectionString().".'");
          unset(self::$connections[$key]);
        }
      }
    }

    public static function loadConnectionFile($file, $connect = false) {
      // Parse the files using an ini parser.
      $connection = parse_ini_file($file, true);
      Logger::debug(var_export($connection, true));

      // Require these items to be defined.
      if (isset($connection['address']) && isset($connection['port'])) {
        // Define optional parameters to their default values.
        if (!isset($connection['ssl'])) {
          $connection['ssl'] = false;
        }

        if (!isset($connection['options'])
            || !is_array($connection['options'])) {
          $connection['options'] = array();
        }

        // Assign the file path used to load this connection.
        $connection['options']['file'] = realpath($file);

        // Restrict possible data types for certain values.
        $connection['port'] = intval($connection['port']);
        $connection['ssl'] = (bool)$connection['ssl'];

        // Add the network to the connection manager.
        return self::newConnection(new Connection("0",
          array($connection['address'], $connection['port'], $connection['ssl'],
          $connection['options'])), $connect);
      }
      else {
        // Uh-oh!
        Logger::debug("Connection in file \"".$file."\" failed to parse.");
      }
      return false;
    }
  }
?>
