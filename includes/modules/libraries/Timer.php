<?php
	class @@CLASSNAME@@ {
		public $name = "Timer";
		private $timers = array();
		
		public function connectionLoopEnd() {
			foreach ($this->timers as $id => $timer) {
				if ($timer != null && $timer["runtime"] <= time()) {
					$class = $timer["class"];
					$callback = $timer["callback"];
					
					$class->$callback($timer["params"]);
					
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
					"runtime" => (time() + $dtime),
					"class" => $class,
					"callback" => $callback,
					"params" => $params
				);
				
				return $i;
			}
			return false;
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("connectionLoopEnd", $this, "connectionLoopEnd");
			return true;
		}
	}
?>