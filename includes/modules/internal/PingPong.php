<?php
	class @@CLASSNAME@@ {
		public $name = "PingPong";
		
		public function respondToPing($name, $data) {
			$connection = $data[0];
			$ex = $data[2];
			$data = $data[1];
			
			if ($ex[0] == "PING") {
				$connection->send("PONG ".$ex[1]);
			}
			return true;
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("rawEvent", $this, "respondToPing");
			return true;
		}
	}
?>