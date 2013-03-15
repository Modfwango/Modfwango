<?php
	class @@CLASSNAME@@ {
		public $name = "Nick";
		
		public function receiveRaw($name, $data) {
			$connection = $data[0];
			$ex = $data[2];
			$data = $data[1];
			
			if (strtolower($ex[0]) == "nick") {
				if (count($ex) > 1) {
					if (!preg_match("/[^0-9a-zA-Z\\-[\\\\\]\\^_`{|}]/", $ex[1])) {
						$connection->nick = $ex[1];
						
						if ($connection->ident == null) {
							// Host notice
						}
						else {
							// Burst
						}
					}
					else {
						if ($connection->nick != null) {
							$connection->send(":pearl.tinycrab.net 432 ".$connection->nick." ".$ex[1]." :Erroneous Nickname");
						}
						else {
							$connection->send(":pearl.tinycrab.net 432 * ".$ex[1]." :Erroneous Nickname");
						}
					}
				}
				else {
					if ($connection->nick != null) {
						$connection->send(":test.server.tld 431 ".$connection->nick." :No nickname given");
					}
					else {
						$connection->send(":test.server.tld 431 * :No nickname given");
					}
				}
			}
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("rawEvent", $this, "receiveRaw");
			return true;
		}
	}
?>