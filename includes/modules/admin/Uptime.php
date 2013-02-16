<?php
	class @@CLASSNAME@@ {
		public $name = "Uptime";
		
		public function receiveChannelMessage($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			if (preg_match("/^!uptime(.*)/", $message)) {
				$connection->send("PRIVMSG ".$target." :Uptime:  ".$this->getNiceDuration(time() - __STARTTIME__));
			}
		}
		
		private function getNiceDuration($durationInSeconds) {
			$duration = '';
			$days = floor($durationInSeconds / 86400);
			$durationInSeconds -= $days * 86400;
			$hours = floor($durationInSeconds / 3600);
			$durationInSeconds -= $hours * 3600;
			$minutes = floor($durationInSeconds / 60);
			$seconds = $durationInSeconds - $minutes * 60;

			if($days > 0) {
				$duration .= $days.' days';
			}
			if($hours > 0) {
				$duration .= ' '.$hours.' hours';
			}
			if($minutes > 0) {
				$duration .= ' '.$minutes.' minutes';
			}
			if($seconds > 0) {
				$duration .= ' '.$seconds.' seconds';
			}
			return trim($duration);
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("channelMessageEvent", $this, "receiveChannelMessage");
			return true;
		}
	}
?>