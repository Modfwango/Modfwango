<?php
	class @@CLASSNAME@@ {
		public $name = "UserIdentification";
		private $authorizedUsers = array();
		private $listening = false;
		private $queue = array();
		
		public function receiveNumericEvent($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$numeric = $data[2];
			$target = $data[3];
			$message = $data[4];
			
			if ($this->listening != false) {
				if ($numeric == 330) {
					$message = explode(" ", $message);
					if (count($this->authorizedUsers) > 0) {
						foreach ($this->authorizedUsers as $nsaccount) {
							if (strtolower($message[0]) == strtolower($this->queue[$this->listening][3]) && $message[1] == $nsaccount) {
								$this->queue[$this->listening][4] = true;
								$module = $this->queue[$this->listening][1];
								$callback = $this->queue[$this->listening][2];
								$module->$callback($this->queue[$this->listening][0], $this->listening, $this->queue[$this->listening][3], true);
							}
						}
					}
				}
				elseif ($numeric == 318) {
					$message = explode(" ", $message);
					if (strtolower($message[0]) == strtolower($this->queue[$this->listening][3]) && !isset($this->queue[$this->listening][4])) {
						$module = $this->queue[$this->listening][1];
						$callback = $this->queue[$this->listening][2];
						$module->$callback($this->queue[$this->listening][0], $this->listening, $this->queue[$this->listening][3], false);
					}
					
					unset($this->queue[$this->listening]);
					$this->listening = false;
					
					if (count($this->queue) > 0) {
						$id = 0;
						while (!isset($this->queue[$id])) {
							$id++;
						}
						
						$this->listening = $id;
						$connection->send("WHOIS ".$this->queue[$id][3]);
					}
				}
			}
		}
		
		public function testLogin($connection, $module, $callback, $nick) {
			$id = 1;
			while (isset($this->queue[$id])) {
				$id++;
			}
			
			$this->queue[$id] = array($connection, $module, $callback, $nick);
			if ($this->listening == false) {
				$this->listening = $id;
				$connection->send("WHOIS ".$nick);
			}
			return $id;
		}
		
		private function loadAuthorizedUsers() {
			$contents = StorageHandling::loadFile($this, "authorizedUsers.txt");
			if (strlen($contents) > 0) {
				if (stristr($contents, "\n")) {
					$this->authorizedUsers = explode("\n", $contents);
				}
				else {
					$this->authorizedUsers = array($contents);
				}
				return true;
			}
			
			return false;
		}
		
		public function isInstantiated() {
			$this->loadAuthorizedUsers();
			EventHandling::registerForEvent("numericEvent", $this, "receiveNumericEvent", 330); // Numeric for "X :is logged in as"
			EventHandling::registerForEvent("numericEvent", $this, "receiveNumericEvent", 318); // End of WHOIS
			return true;
		}
	}
?>