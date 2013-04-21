<?php
	class @@CLASSNAME@@ {
		public $name = "Timer";
		private $timers = array();
		
		public function connectionLoopEnd() {
			foreach ($this->timers as $id => $timer) {
				if ($timer != null && $timer["runtime"] <= microtime(true)) {
					Logger::debug("Processing timer for '".$timer["class"]->name."->".$timer["callback"]."()'");
					$class = $timer["class"];
					$callback = $timer["callback"];
					
					if (isset($class->name)) {
						$mod = ModuleManagement::getModuleByName($class->name);
						if (is_object($mod)) {
							if (get_class($mod) == get_class($class)) {
								$class->$callback($timer["params"]);
							}
							else {
								Logger::info("Kept from resurrecting potentially old (unloaded) code.  Module's class name does not match original. (".get_class($mod)." -> ".get_class($class).")");
							}
						}
					}
					
					$this->timers[$id] = null;
				}
			}
			return true;
		}
		
		public function newTimer($dtime, $class, $callback, $params) {
			if (is_numeric($dtime) && $dtime > -1 && is_object($class) && method_exists($class, $callback)) {
				$i = 1;
				while (isset($this->timers[$i])) {
					$i++;
				}
				
				$this->timers[$i] = array(
					"runtime" => (microtime(true) + $dtime),
					"class" => $class,
					"callback" => $callback,
					"params" => $params
				);
				
				if (isset($class->name)) {
					Logger::debug("Timer created for '".$class->name."->".$callback."()' for ".$dtime." seconds.");
				}
				else {
					Logger::debug("Timer created for '".$callback."()' for ".$dtime." seconds.");
				}
				
				return $i;
			}
			return false;
		}
		
		public function preprocessEvent($name, $registrations, $connection, $data) {
			return true;
		}
		
		public function isInstantiated() {
			EventHandling::createEvent("connectionLoopEnd", $this, "preprocessEvent");
			EventHandling::registerForEvent("connectionLoopEnd", $this, "connectionLoopEnd");
			return true;
		}
	}
?>