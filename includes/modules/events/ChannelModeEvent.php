<?php
	class @@CLASSNAME@@ {
		public $name = "ChannelModeEvent";
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$preprocessors = $registrations[1];
			$registrations = $registrations[0];
			$ex = explode(" ", trim($data));
			if ($ex[1] == "MODE" && substr($ex[2], 0, 1) == "#") {
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
			EventHandling::createEvent("channelModeEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>