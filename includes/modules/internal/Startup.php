<?php
	class @@CLASSNAME@@ {
		public $name = "Startup";
		
		public function receiveNumericEvent($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$numeric = $data[2];
			$target = $data[3];
			$message = $data[4];
			
			if ($numeric == 001) {
				$connection->identify();
			}
			else {
				$connection->joinChannels();
			}
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("numericEvent", $this, "receiveNumericEvent", 001);
			EventHandling::registerForEvent("numericEvent", $this, "receiveNumericEvent", 376);
			EventHandling::registerForEvent("numericEvent", $this, "receiveNumericEvent", 422);
			return true;
		}
	}
?>