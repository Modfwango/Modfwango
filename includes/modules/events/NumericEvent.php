<?php
	class @@CLASSNAME@@ {
		public $name = "NumericEvent";
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			$preprocessors = $registrations[1];
			$registrations = $registrations[0];
			$ex = explode(" ", trim($data));
			if (is_numeric($ex[1]) && strlen($ex[1]) == 3) {
				$source = substr($ex[0], 1);
				$numeric = $ex[1];
				$target = $ex[2];
				unset($ex[0]);
				unset($ex[1]);
				unset($ex[2]);
				$message = implode(" ", $ex);
				
				foreach ($registrations as $id => $registration) {
					if (intval($registration[2]) == intval($numeric)) {
						EventHandling::triggerEvent($name, $id, array($connection, $source, $numeric, $target, $message));
					}
				}
			}
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("numericEvent", $this, "preprocessEvent");
			return true;
		}
	}
?>