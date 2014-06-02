<?php
  class Connection {
    private $socket = null;

    public function __construct($socket) {
      if (is_resource($socket)) {
        $this->socket = $socket;
        return true;
      }
      return false;
    }

    public function disconnect() {
      if (is_resource($this->socket)) {
        // Close the socket.
        Logger::debug("Disconnecting from '".$this->getConnectionString().".'");

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
      return false;
    }

		public function getData() {
			// Check to make sure the socket is a valid resource.
			if (is_resource($this->socket)) {
				// Attempt to read data from the socket and disconnect if there is an
				// error.
				if (($data = @socket_read($this->socket, 8192)) === false
						&& socket_last_error($this->socket) != 11) {
					$this->disconnect();
				}
				else {
					// Return data if it isn't null.
					if ($data != false && strlen($data) > 0) {
						Logger::debug("Data received from client:  '".$data."'");
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
      // Check to make sure the socket is a valid resource.
      if (is_resource($this->socket)) {
        // Retrieve hostname.
        socket_getpeername($this->socket, $address);
        return gethostbyaddr($address);
      }
      return false;
    }

		public function getIP() {
			// Check to make sure the socket is a valid resource.
			if (is_resource($this->socket)) {
				// Retrieve IP address.
				socket_getpeername($this->socket, $address);
				return gethostbyname($address);
			}
			return false;
		}

		public function getPort() {
			// Check to make sure the socket is a valid resource.
			if (is_resource($this->socket)) {
				// Retrieve IP address.
				socket_getpeername($this->socket, $address, $port);
				return $port;
			}
			return false;
		}

    public function send($data, $newline = true) {
      // Check to make sure the socket is a valid resource.
      if (is_resource($this->socket)) {
        Logger::debug("Sending data to client:  '".$data."'");
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
  }
?>
