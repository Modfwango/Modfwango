<?php
  class Socket {
    private $configured = false;
    private $socket = null;
    private $host = null;
    private $port = null;

    public function __construct($host, $port) {
      // Verify type restrictions.
      if (is_string($host) && is_numeric($port)) {
        // Assign class properties.
        $this->host = $host;
        $this->port = $port;
        // Create the socket.
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        // Attempt to bind the socket to a host and port.
        if (@socket_bind($this->socket, $this->host, $this->port)) {
          // Setup the socket to listen and be non-blocking.
          socket_listen($this->socket);
          socket_set_nonblock($this->socket);
          $this->configured = true;
        }
      }
      // Problem with type or binding.
      return $this->configured;
    }

    public function configured() {
      return $this->configured;
    }

    public function getHost() {
      return $this->host;
    }

    public function getPort() {
      return $this->port;
    }

    public function getSocketString() {
      // Build a socket string to identify this socket.
      return $this->host.":".$this->port;
    }

    public function accept() {
      // Accept a new client.
      $client = @socket_accept($this->socket);
      // Make sure an actual client was accepted.
      if ($client != false) {
        // Add the new socket to the connection management class.
        ConnectionManagement::newConnection(new Connection($client,
          $this->port));
        return true;
      }
      // No new client/error accepting client.
      return false;
    }
  }
?>
