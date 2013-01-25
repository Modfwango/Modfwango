<?php
	class @@CLASSNAME@@ {
		public $name = "ChannelQuitEvent";
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$ex = explode(" ", trim($data));
			if ($ex[1] == "QUIT" && substr($ex[2], 0, 1) == "#") {
				$nick = explode("!", $ex[0]);
				$user = explode("@", $nick[1]);
				$nick = substr($nick[0], 1);
				$host = $user[1];
				$user = $user[0];
				$source = array($nick, $user, $host);
				$target = $ex[2];
				unset($ex[0]);
				unset($ex[1]);
				unset($ex[2]);
				$message = substr(implode(" ", $ex), 1);
				
				foreach ($registrations as $id => $registration) {
					EventHandling::triggerEvent($name, $id, array($connection, $source, $target, $message));
				}
			}
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("channelQuitEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>