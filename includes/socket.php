<?php
  class Socket {
    private $configured = false;
    private $socket = null;
    private $host = null;
    private $port = null;
    private $ssl = false;

    public function __construct($host, $port) {
      // Verify type restrictions.
      if (is_string($host) && !stristr($host, "/") && is_numeric($port)) {
        // Assign class properties.
        $this->host = $host;
        $this->port = $port;
        if (substr($this->port, 0, 1) == "+") {
          $this->port = substr($this->port, 1);
          $this->ssl = true;
          $ctx = $this->loadCertificates();
          if ($ctx == false) {
            return false;
          }
        }
        // Attempt to bind the socket to a host and port.
        if ($this->ssl == true) {
          $socket = @stream_socket_server("tls://".$this->host.":".$this->port,
            $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $ctx);
        }
        else {
          $socket = @stream_socket_server("tcp://".$this->host.":".$this->port);
        }
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

    public function close() {
      // Close the socket.
      Logger::info("Closing '".$this->getSocketString().".'");

      // Destroy the socket.
      @stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
      @fclose($this->socket);
      $this->socket = null;
      return true;
    }

    public function configured() {
      return $this->configured;
    }

    private function createSSLCert($file) {
      Logger::info("Generating SSL certificate for \"".
        $this->getSocketString()."\"");

      $dn = array(
        "countryName" => "US",
        "stateOrProvinceName" => "Illinois",
        "localityName" => "Chicago",
        "organizationName" => "Your Company",
        "organizationalUnitName" => "Server",
        "commonName" => "example.org",
        "emailAddress" => "email@example.org"
      );

      $privkey = openssl_pkey_new();
      $cert = openssl_csr_new($dn, $privkey);
      $cert = openssl_csr_sign($cert, null, $privkey, 5840);

      $pem = array();
      openssl_x509_export($cert, $pem[0]);
      openssl_pkey_export($privkey, $pem[1], null);
      $pem = implode($pem);

      file_put_contents($file, $pem);
      chmod($file, 0600);
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

    private function loadCertificates() {
      if (!file_exists(__PROJECTROOT__."/conf")) {
        mkdir(__PROJECTROOT__."/conf");
      }
      if (!file_exists(__PROJECTROOT__."/conf/ssl")) {
        mkdir(__PROJECTROOT__."/conf/ssl");
      }
      if (!file_exists(__PROJECTROOT__."/conf/ssl/".$this->port)) {
        mkdir(__PROJECTROOT__."/conf/ssl/".$this->port);
      }
      if (!file_exists(__PROJECTROOT__."/conf/ssl/".$this->port."/".
          $this->host.".pem")) {
        $this->createSSLCert(__PROJECTROOT__."/conf/ssl/".$this->port."/".
          $this->host.".pem");
      }

      if (file_exists(__PROJECTROOT__."/conf/ssl/".$this->port."/".
          $this->host.".pem")) {
        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", __PROJECTROOT__.
          "/conf/ssl/".$this->port."/".$this->host.".pem");
        stream_context_set_option($ctx, "ssl", "passphrase", null);
        stream_context_set_option($ctx, "ssl", "allow_self_signed", true);
        stream_context_set_option($ctx, "ssl", "verify_peer", false);
        return $ctx;
      }
      return false;
    }

    public function accept() {
      // Accept a new client.
      $client = @stream_socket_accept($this->socket, 0);
      // Make sure an actual client was accepted.
      if (is_resource($client)) {
        // Enable crypto.
        stream_socket_enable_crypto($client, true,
          STREAM_CRYPTO_METHOD_TLS_SERVER);
        // Set non-blocking.
        stream_set_blocking($client, 0);
        // Add the new socket to the connection management class.
        ConnectionManagement::newConnection(new Connection("1", array($client,
          $this->port, $this->ssl, array())));
        return true;
      }
      // No new client/error accepting client.
      return false;
    }
  }
?>
