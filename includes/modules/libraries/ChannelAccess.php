<?php
	class @@CLASSNAME@@ {
		public $name = "ChannelAccess";
		private $listening = false;
		private $queue = array();
		
		public function receiveEvent($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$numeric = $data[2];
			$target = $data[3];
			$message = $data[4];
			
			if ($this->listening != false) {
				if ($numeric == 319) {
					$message = explode(" ", $message);
					if (strtolower($message[0]) == strtolower($this->queue[$this->listening][3])) {
						foreach ($message as $id => $val) {
							if ($id > 0) {
								if (substr($val, 0, 1) == ":") {
									$val = substr($val, 1);
								}
								
								if (in_array(substr($val, 0, 1), array("+", "%", "@", "&", "~"))) {
									if (!isset($this->queue[$this->listening][4])) {
										$this->queue[$this->listening][4] = array();
									}
									$access = array();
									while (substr($val, 0, 1) != "#" && $val != null) {
										$access[] = substr($val, 0, 1);
										$channel = substr($val, 1);
										$val = $channel;
									}
									
									foreach ($access as $mode) {
										$this->queue[$this->listening][4][] = array($mode, $channel);
									}
								}
							}
						}
					}
				}
				elseif ($numeric == 318) {
					$message = explode(" ", $message);
					if (strtolower($message[0]) == strtolower($this->queue[$this->listening][3]) && isset($this->queue[$this->listening][4])) {
						$module = $this->queue[$this->listening][1];
						$callback = $this->queue[$this->listening][2];
						$module->$callback($this->queue[$this->listening][0], $this->listening, $this->queue[$this->listening][3], $this->queue[$this->listening][4]);
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
		
		public function getAccess($connection, $module, $callback, $nick) {
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
		
		public function isInstantiated() {
			EventHandling::registerForEvent("numericEvent", $this, "receiveEvent", 319); // Numeric for "X :@#torrents @#channel +#terminal"
			EventHandling::registerForEvent("numericEvent", $this, "receiveEvent", 318); // End of WHOIS
			return true;
		}
	}
?>