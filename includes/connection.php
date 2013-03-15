<?php
	class Connection {
		private $socket = null;
		public $nick = null;
		public $ident = null;
		public $realname = null;
		
		public function __construct($socket) {
			if (is_resource($socket)) {
				$this->socket = $socket;
				return true;
			}
			return false;
		}
		
		public function kill() {
			// Logger::debug("Killing client.");
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
			socket_write($this->socket, trim($data)."\n"); // Send data
		}
	}
?>