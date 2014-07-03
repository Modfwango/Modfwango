<?php
  class Connection {
    private $socket = null;
    private $address = null;
    private $port = null;
    private $t = null;
    private $options = array();

    public function __construct($socket) {
      if (is_resource($socket)) {
        $this->socket = $socket;
        socket_getpeername($this->socket, $address, $port);
        $this->address = $address;
        $this->port = $port;

        // Let people know what's going on.
        Logger::info("Connection to '".$this->address.":".$this->port.
          "' created.");

        // Iterate through each event to find the connectionCreatedEvent
        // event.
        foreach (EventHandling::getEvents() as $key => $event) {
          if ($key == "connectionCreatedEvent") {
            foreach ($event[2] as $id => $registration) {
              // Trigger the connectionCreatedEvent event for each registered
              // module.
              if (EventHandling::triggerEvent("connectionCreatedEvent", $id,
                  $this)) {
                return true;
              }
            }
          }
        }
      }
      return false;
    }

    public function disconnect() {
      // Close the socket.
      Logger::info("Disconnecting from '".$this->getConnectionString().".'");

      // Destroy the socket.
      @socket_shutdown($this->socket);
      @socket_close($this->socket);
      $this->socket = null;

      // Iterate through each event to find the connectionConnectedEvent
      // event.
      foreach (EventHandling::getEvents() as $key => $event) {
        if ($key == "connectionDisconnectedEvent") {
          foreach ($event[2] as $id => $registration) {
            // Trigger the connectionDisconnectedEvent event for each
            // registered module.
            EventHandling::triggerEvent("connectionDisconnectedEvent", $id,
              $this);
          }
        }
      }
      return true;
    }

    public function getData() {
      // Check to make sure the socket is a valid resource.
      if (is_resource($this->socket)) {
        // Attempt to read data from the socket.
        if ($data = @socket_read($this->socket, 8192)) {
          if ($data != false && strlen($data) > 0) {
            // Return the data.
            Logger::debug("Data received on '".$this->getConnectionString().
              "':  '".$data."'");
            return $data;
          }
        }
      }
      return false;
    }

    public function getConnectionString() {
      // Build a connection string to identify this connection.
      return $this->getHost().":".$this->getPort();
    }

    public function getHost() {
      // Retrieve hostname.
      return gethostbyaddr($this->address);
    }

    public function getIP() {
      // Retrieve IP address.
      return gethostbyname($this->address);
    }

    public function getOption($key) {
      // Retrieve the requested option if it exists, otherwise return false.
      return (isset($this->options[$key]) ? $this->options[$key] : false);
    }

    public function getPort() {
      // Retrieve IP address.
      return $this->port;
    }

    public function send($data, $newline = true) {
      // Check to make sure the socket is a valid resource.
      if (is_resource($this->socket)) {
        if (trim($data) != null) {
          Logger::debug("Sending data to client:  '".$data."'");
        }
        // Send data to the client.
        if ($newline == true) {
          $status = @socket_write($this->socket, $data."\n");
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
