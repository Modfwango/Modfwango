<?php
	class @@CLASSNAME@@ {
		public $name = "Power";
		private $queue = array();
		
		public function receiveChannelMessage($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			$ex = explode(" ", $message);
			if (strtolower($ex[0]) == "!restart") {
				$module = ModuleManagement::getModuleByName("UserIdentification");
				if (is_object($module)) {
					$this->queue[$module->testLogin($connection, $this, "userLoginCallback", $source[0])] = array($source[0], $ex[0]);
				}
			}
			
			if (strtolower($ex[0]) == "!stop") {
				$module = ModuleManagement::getModuleByName("UserIdentification");
				if (is_object($module)) {
					$this->queue[$module->testLogin($connection, $this, "userLoginCallback", $source[0])] = array($source[0], $ex[0]);
				}
			}
		}
		
		function userLoginCallback($connection, $id, $nick, $loggedin) {
			$entry = $this->queue[$id];
			if ($loggedin == true) {
				if (strtolower($entry[1]) == "!restart") {
					$this->restart();
				}
			
				if (strtolower($entry[1]) == "!stop") {
					die($this->stop());
				}
			}
			else {
				$connection->send("NOTICE ".$entry[0]." :You are not authorized to use this command.");
			}
		}
		
		public function restart(){
			$this->stop();
			die($this->start());
		}
		
		public function start(){
			exec("screen -dm php ".__PROJECTROOT__."/main.php");
		}
		
		public function stop(){
			foreach (ConnectionManagement::getConnections() as $connection) {
				$connection->send("QUIT :Shutting down...");
			}
			sleep(1);
			return null;
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("channelMessageEvent", $this, "receiveChannelMessage");
			return true;
		}
	}
?>