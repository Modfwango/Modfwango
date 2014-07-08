<?php
  class Connection {
    private $socket = null;
    private $ip = null;
    private $host = null;
    private $localip = null;
    private $localhost = null;
    private $port = null;
    private $options = array();

    public function __construct($socket, $port) {
      if (is_resource($socket)) {
        $this->socket = $socket;
        $localip = explode(":", stream_socket_get_name($this->socket, false));
        $localip = $localip[0];
        $ip = explode(":", stream_socket_get_name($this->socket, true));
        $ip = $ip[0];
        $this->ip = $ip;
        $this->host = $this->gethostbyaddr_cached($ip);
        $this->localip = $localip;
        $this->localhost = $this->gethostbyaddr_cached($localip);
        $this->port = $port;

        // Let people know what's going on.
        Logger::info("Connection to '".$this->getConnectionString().
          "' created.");

        // Get the connectionCreatedEvent event.
        $event = EventHandling::getEventByName("connectionCreatedEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the connectionCreatedEvent event for each registered
            // module.
            if (EventHandling::triggerEvent("connectionCreatedEvent", $id,
                $this)) {
              $this->configured = true;
            }
          }
        }
      }
      return false;
    }

    public function disconnect() {
      if ($this->socket != null) {
        // Close the socket.
        Logger::info("Disconnecting from '".$this->getConnectionString().".'");

        // Destroy the socket.
        @socket_shutdown($this->socket);
        @socket_close($this->socket);
        $this->socket = null;

        // Get the connectionConnectedEvent event.
        $event = EventHandling::getEventByName("connectionDisconnectedEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the connectionDisconnectedEvent event for each registered
            // module.
            EventHandling::triggerEvent("connectionDisconnectedEvent", $id,
              $this);
          }
        }
        return true;
      }
      return false;
    }

    private function fetch_a($a) {
      $tmp = dns_get_record($a.".", DNS_A);
      if (is_array($tmp) && count($tmp) > 0) {
        return $tmp;
      }
      return $a;
    }

    private function fetch_ptr($a) {
      $tmp = dns_get_record(implode(".", array_reverse(explode(".", $a))).
        ".in-addr.arpa.", DNS_PTR);
      if (is_array($tmp) && count($tmp) > 0) {
        foreach ($tmp as $entry) {
          $ar = $this->fetch_a($entry["target"]);
          if (is_array($ar)) {
            foreach ($ar as $ip) {
              if ($ip["ip"] == $a) {
                return $entry["target"];
              }
            }
          }
        }
      }
      return $a;
    }

    public function getData() {
      // Check to make sure the socket is a valid resource.
      if (is_resource($this->socket)) {
        // Attempt to read data from the socket.
        if ($data = @fgets($this->socket, 8192)) {
          if ($data != false && strlen($data) > 0) {
            // Return the data.
            Logger::debug("Data received on '".$this->getConnectionString().
              "':  '".$data."'");
            return $data;
          }
        }
        elseif (feof($this->socket)) {
          // Kill the socket if it should die upon no data.
          Logger::info("Socket died");
          $this->disconnect();
        }
      }
      return false;
    }

    public function getConnectionString() {
      // Build a connection string to identify this connection.
      return $this->getHost().":".$this->getPort();
    }

    public function gethostbyaddr_cached($a) {
      global $dns_cache;
      if (isset($dns_cache[$a])) {
        return $dns_cache[$a];
      }
      else {
        $temp = $this->fetch_ptr($a);
        $dns_cache[$a] = $temp;
        return $temp;
      }
    }

    public function getHost() {
      // Retrieve hostname.
      return $this->host;
    }

    public function getIP() {
      // Retrieve IP.
      return $this->ip;
    }

    public function getLocalHost() {
      // Retrieve hostname.
      return $this->localhost;
    }

    public function getLocalIP() {
      // Retrieve IP.
      return $this->localip;
    }

    public function getOption($key) {
      // Retrieve the requested option if it exists, otherwise return false.
      return (isset($this->options[$key]) ? $this->options[$key] : false);
    }

    public function getPort() {
      // Retrieve port.
      return $this->port;
    }

    public function isAlive() {
      if (is_resource($this->socket)) {
        return true;
      }
      return false;
    }

    public function send($data, $newline = true) {
      // Check to make sure the socket is a valid resource.
      if (is_resource($this->socket)) {
        if (trim($data) != null) {
          Logger::debug("Sending data to client:  '".$data."'");
        }
        // Send data to the client.
        if ($newline == true) {
          $status = @socket_write($this->socket, $data."\r\n");
        }
        else {
          $status = @socket_write($this->socket, $data);
        }

        // Disconnect if an error occurred.
        if ($status === false) {
          $this->disconnect();
        }
        else {
          return true;
        }
      }
      return false;
    }

    public function setOption($key, $value) {
      // Set an option for this connection.
      $this->options[$key] = $value;
      return true;
    }
  }
?>
