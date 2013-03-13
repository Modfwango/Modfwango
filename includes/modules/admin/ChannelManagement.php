<?php
	class @@CLASSNAME@@ {
		public $name = "ChannelManagement";
		private $queue = array();
		
		public function receiveChannelMessage($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			if (preg_match("/^!join (.*)/", $message, $matches)) {
				$module = ModuleManagement::getModuleByName("UserIdentification");
				if (is_object($module)) {
					if (stristr($matches[1], ",")) {
						$matches[1] = explode(",", $matches[1]);
					}
					else {
						$matches[1] = array($matches[1]);
					}
					$this->queue[$module->testLogin($connection, $this, "userLoginCallback", $source[0])] = array($source, $target, array("JOIN", $matches[1]));
				}
			}

			if (preg_match("/^!part (.*)/", $message, $matches)) {
				$module = ModuleManagement::getModuleByName("UserIdentification");
				if (is_object($module)) {
					if (stristr($matches[1], ",")) {
						$matches[1] = explode(",", $matches[1]);
					}
					else {
						$matches[1] = array($matches[1]);
					}
					$this->queue[$module->testLogin($connection, $this, "userLoginCallback", $source[0])] = array($source, $target, array("PART", $matches[1]));
				}
			}

			if (preg_match("/^!autojoin (.*)/", $message, $matches)) {
				$module = ModuleManagement::getModuleByName("UserIdentification");
				if (is_object($module)) {
					if (stristr($matches[1], ",")) {
						$matches[1] = explode(",", $matches[1]);
					}
					else {
						$matches[1] = array($matches[1]);
					}
					$this->queue[$module->testLogin($connection, $this, "userLoginCallback", $source[0])] = array($source, $target, array("JOIN", $matches[1], true));
				}
			}

			if (preg_match("/^!autopart (.*)/", $message, $matches)) {
				$module = ModuleManagement::getModuleByName("UserIdentification");
				if (is_object($module)) {
					if (stristr($matches[1], ",")) {
						$matches[1] = explode(",", $matches[1]);
					}
					else {
						$matches[1] = array($matches[1]);
					}
					$this->queue[$module->testLogin($connection, $this, "userLoginCallback", $source[0])] = array($source, $target, array("PART", $matches[1], true));
				}
			}
		}
		
		public function userLoginCallback($connection, $id, $nick, $loggedin) {
			$entry = $this->queue[$id];
			if ($loggedin == true) {
				if ($entry[2][0] == "JOIN") {
					if (isset($entry[2][2]) && $entry[2][2] == true) {
						$this->autojoinAdd($connection->getNetworkName(), $entry[2][1]);
					}
					$connection->send("JOIN ".implode(",", $entry[2][1]));
					$connection->send("NOTICE ".$entry[0][0]." :I have joined the channel(s) \"".implode(",", $entry[2][1])."\"");
				}
				elseif ($entry[2][0] == "PART") {
					if (isset($entry[2][2]) && $entry[2][2] == true) {
						$this->autojoinRemove($connection->getNetworkName(), $entry[2][1]);
					}
					$connection->send("PART ".implode(",", $entry[2][1]));
					$connection->send("NOTICE ".$entry[0][0]." :I have left the channel(s) \"".implode(",", $entry[2][1])."\"");
				}
			}
			else {
				$connection->send("NOTICE ".$entry[0][0]." :You are not authorized to use this command.");
			}
		}
		
		private function autojoinAdd($network, $channels) {
			$autojoins = StorageHandling::loadFile($this, $network."-autojoin.txt");
			if ($autojoins != false && is_string($autojoins) && strlen($autojoins) > 0) {
				$autojoins = unserialize($autojoins);
				if (is_array($autojoins)) {
					foreach ($channels as $channel) {
						Logger::info("Adding '".$channel."' to autojoin for '".$network.".'");
						$autojoins[] = $channel;
					}
					StorageHandling::saveFile($this, $network."-autojoin.txt", serialize($autojoins));
				}
			}
			else {
				StorageHandling::saveFile($this, $network."-autojoin.txt", serialize(array()));
			}
		}
		
		private function autojoinRemove($network, $channels) {
			$autojoins = StorageHandling::loadFile($this, $network."-autojoin.txt");
			if ($autojoins != false && is_string($autojoins) && strlen($autojoins) > 0) {
				$autojoins = unserialize($autojoins);
				if (is_array($autojoins)) {
					foreach ($channels as $channel) {
						foreach ($autojoins as $key => $channel1) {
							if (strtolower($channel) == strtolower($channel1)) {
								Logger::info("Removing '".$channel."' from autojoin for '".$network.".'");
								unset($autojoins[$key]);
							}
						}
					}
					StorageHandling::saveFile($this, $network."-autojoin.txt", serialize($autojoins));
				}
			}
			else {
				StorageHandling::saveFile($this, $network."-autojoin.txt", serialize(array()));
			}
		}
		
		public function autojoinChannels($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$numeric = $data[2];
			$target = $data[3];
			$message = $data[4];
			
			$channels = StorageHandling::loadFile($this, $connection->getNetworkName()."-autojoin.txt");
			if ($channels != false && is_string($channels) && strlen($channels) > 0) {
				$channels = unserialize($channels);
				if (is_array($channels)) {
					Logger::info("Loaded autojoin database for \"".$connection->getNetworkName()."\"");
					foreach ($channels as $channel) {
						Logger::info("Autojoining \"".$channel."\" on \"".$connection->getNetworkName()."\"");
						$connection->send("JOIN ".$channel);
					}
				}
			}
			else {
				StorageHandling::saveFile($this, $connection->getNetworkName()."-autojoin.txt", serialize(array()));
			}
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("channelMessageEvent", $this, "receiveChannelMessage");
			EventHandling::registerForEvent("numericEvent", $this, "autojoinChannels", 376);
			EventHandling::registerForEvent("numericEvent", $this, "autojoinChannels", 422);
			return true;
		}
	}
?>