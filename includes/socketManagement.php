<?php
  class SocketManagement {
    private static $sockets = array();

    public static function newSocket($socket) {
      // Verify that the socket parameter is in fact a Socket class
      if (is_object($socket) && get_class($socket) == "Socket"
          && $socket->configured() == true
          && self::getSocketByHost($socket->getHost()) == false) {
        // Store the socket
        self::$sockets[] = $socket;
        Logger::debug(($socket->getIPC() ? "IPC " : null)."Socket '".
          $socket->getSocketString()."' added to the socket manager.");
        return true;
      }
      return false;
    }

    public static function getSocketByHost($host) {
      // See if an index for a particular socket exists with provided host
      $i = self::getSocketIndexByHost($host);
      if ($i != false) {
        // Get the socket at said index and return it
        return self::getSocketByIndex($i);
      }
      return false;
    }

    public static function getSocketByIndex($i) {
      // Check to see if a socket exists at this index
      if (isset(self::$sockets[$i])) {
        return self::$sockets[$i];
      }
      return false;
    }

    public static function delSocketByHost($host) {
      // See if an index for a particular socket exists with provided host
      $i = self::getSocketIndexByHost($host);
      if ($i != false) {
        // Remove this socket by its index
        return self::delSocketByIndex($i);
      }
      return false;
    }

    public static function delSocketByIndex($i) {
      // Check to see if a socket exists at this index
      if (isset(self::$sockets[$i])) {
        // Store socket in a local variable for access after it is removed
        $s = self::$sockets[$i];
        // Remove the socket
        Logger::debug("Socket '".
          self::$sockets[$i]->getSocketString().
          "' removed from the socket manager.");
        unset(self::$sockets[$i]);
        // Disconnect the socket after it's removed
        $s->disconnect();
        return true;
      }
      return false;
    }

    public static function getSocketIndexByHost($host) {
      // Iterate through each socket
      foreach (self::$sockets as $key => $socket) {
        // Check to see if the socket's host matches the provided host
        if (strtolower(trim($socket->getHost()))
            == strtolower(trim($host))) {
          // Return this socket's index
          return $key;
        }
      }
      return false;
    }

    public static function getSockets() {
      return self::$sockets;
    }
  }
?>
