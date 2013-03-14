<?php
	class Connection {
		private $socket = null;
		private $nickname = null;
		private $ident = null;
		private $realname = null;
		
		public function __construct($socket) {
			if (is_resource($socket)) {
				$this->socket = $socket;
				
				Logger::debug("New connection.");
				return true;
			}
			return false;
		}
		
		public function kill() {
			Logger::debug("Killing client.");
			// close socket
		}
		
		public function getData() {
			$data = trim(@socket_read($this->socket, 8192, PHP_NORMAL_READ));
			if ($data != false && strlen($data) > 0) {
				Logger::debug("Data received from client:  '".$data."'");
				return $data;
			}
			else {
				return false;
			}
		}
		
		public function send($data) {
			Logger::debug("Sending data to client:  '".$data."'");
			// fputs($this->socket, trim($data)."\n"); // Send data
		}
	}
?>