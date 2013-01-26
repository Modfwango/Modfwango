<?php
	class @@CLASSNAME@@ {
		public $name = "ChannelQuitEvent";
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$ex = explode(" ", trim($data));
			if ($ex[1] == "QUIT") {
				$nick = explode("!", $ex[0]);
				$user = explode("@", $nick[1]);
				$nick = substr($nick[0], 1);
				$host = $user[1];
				$user = $user[0];
				$source = array($nick, $user, $host);
				unset($ex[0]);
				unset($ex[1]);
				$message = substr(implode(" ", $ex), 1);
				
				foreach ($registrations as $id => $registration) {
					EventHandling::triggerEvent($name, $id, array($connection, $source, $message));
				}
			}
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("channelQuitEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>