<?php
	class @@CLASSNAME@@ {
		public $name = "User";
		
		public function receiveRaw($name, $data) {
			$connection = $data[0];
			$ex = $data[2];
			$data = $data[1];
			
			if (strtolower($ex[0]) == "user") {
				if (count($ex) > 4) {
					if ($connection->ident == null) {
						if (!preg_match("/[^0-9a-zA-Z\\-.[\\\\\]\\^_`{|}~]/", $ex[1])) {
							if ($connection->ident == null) {
								if ($connection->nick == null) {
									// Host notice
								}
								else {
									// Burst
								}
							}
							$connection->ident = $ex[1];
							
							if (substr($ex[4], 0, 1) == ":") {
								$ex[4] = substr($ex[4], 1);
							}
							
							for ($i = 4; $i < count($ex); $i++) {
								$connection->realname .= " ".$ex[$i];
							}
							$connection->realname = substr($connection->realname, 1);
							
							Logger::info("Client registered with username \"".$connection->ident."\" and realname \"".$connection->realname."\"");
						}
						else {
							if ($connection->nick != null) {
								$connection->send(":test.server.tld NOTICE ".$connection->nick." :*** Your username is invalid. Please make sure that your username contains only alphanumeric characters.");
							}
							else {
								$connection->send(":test.server.tld NOTICE * :*** Your username is invalid. Please make sure that your username contains only alphanumeric characters.");
							}
						}
					}
					elseif ($connection->nick != null) {
						$connection->send(":test.server.tld 462 ".$connection->nick." :You may not reregister");
					}
				}
				else {
					if ($connection->nick != null) {
						$connection->send(":test.server.tld 461 ".$connection->nick." USER :Not enough parameters");
					}
					else {
						$connection->send(":test.server.tld 461 * USER :Not enough parameters");
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