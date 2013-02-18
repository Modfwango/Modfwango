<?php
	class @@CLASSNAME@@ {
		public $name = "ChannelNoticeEvent";
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$preprocessors = $registrations[1];
			$registrations = $registrations[0];
			$ex = explode(" ", trim($data));
			if ($ex[1] == "NOTICE" && substr($ex[2], 0, 1) == "#" && stristr($ex[0], "@")) {
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
				$notice = substr(implode(" ", $ex), 1);
				
				foreach ($registrations as $id => $registration) {
					EventHandling::triggerEvent($name, $id, array($connection, $source, $target, $notice));
				}
			}
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("channelNoticeEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>