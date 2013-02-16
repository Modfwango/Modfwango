<?php
	class @@CLASSNAME@@ {
		public $name = "UserModeEvent";
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$ex = explode(" ", trim($data));
			if ($ex[1] == "MODE" && substr($ex[2], 0, 1) != "#") {
				if (stristr($ex[0], "@")) {
					$nick = explode("!", substr($ex[0], 1));
					$user = explode("@", $nick[1]);
					$nick = substr($nick[0], 1);
					$host = $user[1];
					$user = $user[0];
					$source = array($nick, $user, $host);
				}
				else {
					$nick = substr($ex[0], 1);
					$source = array($nick);
				}
				$target = $ex[2];
				
				unset($ex[0]);
				unset($ex[1]);
				unset($ex[2]);
				$modestring = implode(" ", $ex);
				if (substr($modestring, 0, 1) == ":") {
					$modestring = substr($modestring, 1);
				}
				
				foreach ($registrations as $id => $registration) {
					EventHandling::triggerEvent($name, $id, array($connection, $source, $target, $modestring));
				}
			}
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("userModeEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>