<?php
	class @@CLASSNAME@@ {
		public $name = "MemoryUsage";
		
		public function receiveChannelMessage($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			if (preg_match("/^!memusage(.*)/", $message)) {
				$connection->send("PRIVMSG ".$target." :".$this->prepareNumber(intval(memory_get_usage() / 1024 / 1024))." MB (".$this->prepareNumber(intval(memory_get_usage() / 1024))." KB)");
			}
		}
		
		private function prepareNumber($num) {
			$num = strval($num);
			if ($num < 4) {
				return $num;
			}
			$commacount = intval(strlen($num) / 3);
			$number = array();
			$i = 1;
			while ($i <= $commacount) {
				$number[] = substr($num, -($i * 3), 3);
				$i++;
			}
			
			$remaining = null;
			if (strlen($num) > (count($number) * 3)) {
				$remaining = substr($num, 0, (strlen($num) - (count($number) * 3))).",";
			}
			return $remaining.implode(",", array_reverse($number));
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("channelMessageEvent", $this, "receiveChannelMessage");
			return true;
		}
	}
?>