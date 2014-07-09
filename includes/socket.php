<?php
  class Socket {
    private $configured = false;
    private $socket = null;
    private $host = null;
    private $port = null;
    private $ssl = false;

    public function __construct($host, $port) {
      // Verify type restrictions.
      if (is_string($host) && is_numeric($port)) {
        // Assign class properties.
        $this->host = $host;
        $this->port = $port;
        // Create the socket.
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        // Attempt to bind the socket to a host and port.
        $socket = @stream_socket_server("tcp://".$this->host.":".$this->port);
        if (is_resource($socket)) {
          // Setup the socket to be non-blocking.
          stream_set_blocking($socket, 0);
          $this->socket = $socket;
          $this->configured = true;
        }
      }
      // Problem with type or binding.
      return $this->configured;
    }

    public function configured() {
      return $this->configured;
    }

    public function close() {
      // Close the socket.
      Logger::info("Closing '".$this->getSocketString().".'");

      // Destroy the socket.
      @stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
      @fclose($this->socket);
      $this->socket = null;
      return true;
    }

    public function getHost() {
      return $this->host;
    }

    public function getPort() {
      return $this->port;
    }

    public function getSocketString() {
      // Build a socket string to identify this socket.
      return ($this->ssl ? "tls://" : "tcp://").$this->host.":".$this->port;
    }

    public function accept() {
      // Accept a new client.
      $client = @stream_socket_accept($this->socket, 0);
      // Make sure an actual client was accepted.
      if (is_resource($client)) {
        // Set non-blocking.
        stream_set_blocking($client, 0);
        // Add the new socket to the connection management class.
        ConnectionManagement::newConnection(new Connection("1", array($client,
          $this->port, false, array())));
        return true;
      }
      // No new client/error accepting client.
      return false;
    }
  }
?>
