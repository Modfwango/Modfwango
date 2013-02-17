<?php
	class @@CLASSNAME@@ {
		public $name = "CleanLogs";
		
		public function receiveChannelJoin($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			
			if (substr($target, 0, 1) == ":") {
				$target = substr($target, 1);
			}
			
			Logger::info("[".$connection->getNetworkName()." / ".$target."] * ".$source[0]."(".$source[1]."@".$source[2].") Join");
		}
		
		public function receiveChannelMessage($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			if (preg_match("/ACTION (.*)/", $message, $matches)) {
				$message = $matches[1];
				Logger::info("[".$connection->getNetworkName()." / ".$target."] * ".$source[0]." ".$message);
			}
			else {
				Logger::info("[".$connection->getNetworkName()." / ".$target."] <".$source[0]."> ".$message);
			}
		}
		
		public function receiveChannelMode($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$modestring = $data[3];
			
			Logger::info("[".$connection->getNetworkName()." / ".$target."] * ".$source[0]."(".$source[1]."@".$source[2].") set mode: ".$modestring);
		}
		
		public function receiveChannelNotice($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			Logger::info("[".$connection->getNetworkName()." / ".$target."] -".$source[0]."- ".$message);
		}
		
		public function receiveChannelPart($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			if ($message != null) {
				$message = " (".$message.")";
			}
			
			Logger::info("[".$connection->getNetworkName()." / ".$target."] * ".$source[0]."(".$source[1]."@".$source[2].") Part".$message);
		}
		
		public function receiveChannelQuit($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$message = $data[2];
			
			if ($message != null) {
				$message = " (".$message.")";
			}
			
			Logger::info("[".$connection->getNetworkName()."] * ".$source[0]."(".$source[1]."@".$source[2].") Quit".$message);
		}
		
		public function receiveChannelTopic($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$topic = $data[3];
			
			Logger::info("[".$connection->getNetworkName()."] * ".$source[0]."(".$source[1]."@".$source[2].") changed the topic to: '".$topic."'");
		}
		
		public function receivePrivateMessage($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			if (preg_match("/ACTION (.*)/", $message, $matches)) {
				$message = $matches[1];
				Logger::info("[".$connection->getNetworkName()." / PM] * ".$source[0]." ".$message);
			}
			else {
				Logger::info("[".$connection->getNetworkName()." / PM] <".$source[0]."> ".$message);
			}
		}
		
		public function receivePrivateNotice($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			Logger::info("[".$connection->getNetworkName()." / PM] -".$source[0]."- ".$message);
		}
		
		public function receiveUserMode($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$modestring = $data[3];
			
			Logger::info("[".$connection->getNetworkName()." / ".$target."] * ".$source[0]." set mode: ".$modestring);
		}
		
		public function isInstantiated() {
			EventHandling::registerForEvent("channelJoinEvent", $this, "receiveChannelJoin");
			EventHandling::registerForEvent("channelMessageEvent", $this, "receiveChannelMessage");
			EventHandling::registerForEvent("channelModeEvent", $this, "receiveChannelMode");
			EventHandling::registerForEvent("channelNoticeEvent", $this, "receiveChannelNotice");
			EventHandling::registerForEvent("channelPartEvent", $this, "receiveChannelPart");
			EventHandling::registerForEvent("channelQuitEvent", $this, "receiveChannelQuit");
			EventHandling::registerForEvent("channelTopicEvent", $this, "receiveChannelTopic");
			EventHandling::registerForEvent("privateMessageEvent", $this, "receivePrivateMessage");
			EventHandling::registerForEvent("privateNoticeEvent", $this, "receivePrivateNotice");
			EventHandling::registerForEvent("userModeEvent", $this, "receiveUserMode");
			return true;
		}
	}
?>