<?php
  class Connection {
    private $socket = null;
    private $configured = false;
    private $ip = null;
    private $host = null;
    private $localip = null;
    private $localhost = null;
    private $port = null;
    private $options = array();
    private $ssl = false;
    private $type = null;

    public function __construct($type, $data) {
      $this->type = $type;
      if ($type == "0") {
        $host = $data[0];
        $port = $data[1];
        $ssl = $data[2];
        $options = $data[3];
        // Verify type restrictions; we don't want anything unexpected.
        if (is_string($host) && is_numeric($port) && is_bool($ssl)
            && is_array($options)) {
          // Assign class properties from construction arguments.
          if (filter_var($host, FILTER_VALIDATE_IP)) {
            $this->ip = $host;
            $this->host = $this->fetch_ptr($this->ip);
          }
          else {
            $this->host = $host;
            $ips = $this->fetch_a($this->host);
            if (is_array($ips)) {
              shuffle($ips);
              $this->ip = $ips[0]["ip"];
            }
            else {
              return false;
            }
          }
          $this->localip = "127.0.0.1";
          $this->localhost = "localhost";
          $this->port = $port;
          $this->ssl = $ssl;
          $this->options = $options;
          return $this->created();
        }
      }
      elseif ($type == "1") {
        $socket = $data[0];
        $port = $data[1];
        $ssl = $data[2];
        $options = $data[3];
        // Verify type restrictions; we don't want anything unexpected.
        if (is_resource($socket) && is_numeric($port) && is_bool($ssl)
            && is_array($options)) {
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
          $this->ssl = $ssl;
          $this->options = $options;
          return $this->created();
        }
      }
      return false;
    }

    public function configured() {
      return $this->configured;
    }

    public function connect() {
      if ($this->type == "0") {
        // Attempt to open a socket to the requested host.
        Logger::debug("Attempting connection to '".
          $this->getConnectionString()."'");
        $this->socket = fsockopen(($this->ssl ? "tls://" : null).$this->host,
          $this->port);

        // Make sure that the stream doesn't block until it receives data.
        stream_set_blocking($this->socket, 0);

        // Get the connectionConnectedEvent event.
        $event = EventHandling::getEventByName("connectionConnectedEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the connectionConnectedEvent event for each registered
            // module.
            EventHandling::triggerEvent("connectionConnectedEvent", $id,
              $this);
          }
        }
        return true;
      }
      return false;
    }

    public function created() {
      // Let people know what's going on.
      Logger::info("Connection to '".$this->getConnectionString().
        "' created.");

      // Get the connectionCreatedEvent event.
      $event = EventHandling::getEventByName("connectionCreatedEvent");
      if ($event != false) {
        if (count($event[2]) > 0) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the connectionCreatedEvent event for each registered
            // module.
            if (EventHandling::triggerEvent("connectionCreatedEvent", $id,
                $this)) {
              $this->configured = true;
            }
          }
        }
        else {
          $this->configured = true;
        }
      }
      return $this->configured;
    }

    public function disconnect() {
      if ($this->socket != null) {
        // Close the socket.
        Logger::info("Disconnecting from '".$this->getConnectionString().".'");

        // Destroy the socket.
        @stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
        @fclose($this->socket);
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
      return ($this->ssl ? "tls://" : "tcp://").$this->getHost().":".
        $this->getPort();
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

    public function getSSL() {
      // Retrieve SSL.
      return $this->ssl;
    }

    public function getType() {
      // Retrieve type.
      return $this->type;
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
          $line = $data."\r\n";
          $status = @fputs($this->socket, $line, strlen($line));
        }
        else {
          $status = @fputs($this->socket, $data, strlen($data));
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
