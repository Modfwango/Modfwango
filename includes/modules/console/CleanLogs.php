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
			
			echo "[".$connection->getNetworkName()." / ".$target."] * ".$source[0]."(".$source[1]."@".$source[2].") Join\n";
		}
		
		public function receiveChannelMessage($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			if (preg_match("/ACTION (.*)/", $message, $matches)) {
				$message = $matches[1];
				echo "[".$connection->getNetworkName()." / ".$target."] * ".$source[0]." ".$message."\n";
			}
			else {
				echo "[".$connection->getNetworkName()." / ".$target."] <".$source[0]."> ".$message."\n";
			}
		}
		
		public function receiveChannelMode($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$modestring = $data[3];
			
			echo "[".$connection->getNetworkName()." / ".$target."] * ".$source[0]."(".$source[1]."@".$source[2].") set mode: ".$modestring."\n";
		}
		
		public function receiveChannelNotice($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			echo "[".$connection->getNetworkName()." / ".$target."] -".$source[0]."- ".$message."\n";
		}
		
		public function receiveChannelPart($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			if ($message != null) {
				$message = " (".$message.")";
			}
			
			echo "[".$connection->getNetworkName()." / ".$target."] * ".$source[0]."(".$source[1]."@".$source[2].") Part".$message."\n";
		}
		
		public function receiveChannelQuit($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$message = $data[2];
			
			if ($message != null) {
				$message = " (".$message.")";
			}
			
			echo "[".$connection->getNetworkName()."] * ".$source[0]."(".$source[1]."@".$source[2].") Quit".$message."\n";
		}
		
		public function receiveChannelTopic($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$topic = $data[3];
			
			echo "[".$connection->getNetworkName()."] * ".$source[0]."(".$source[1]."@".$source[2].") changed the topic to: '".$topic."'\n";
		}
		
		public function receivePrivateMessage($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			if (preg_match("/ACTION (.*)/", $message, $matches)) {
				$message = $matches[1];
				echo "[".$connection->getNetworkName()." / PM] * ".$source[0]." ".$message."\n";
			}
			else {
				echo "[".$connection->getNetworkName()." / PM] <".$source[0]."> ".$message."\n";
			}
		}
		
		public function receivePrivateNotice($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$message = $data[3];
			
			echo "[".$connection->getNetworkName()." / PM] -".$source[0]."- ".$message."\n";
		}
		
		public function receiveUserMode($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			$modestring = $data[3];
			
			echo "[".$connection->getNetworkName()." / ".$target."] * ".$source[0]." set mode: ".$modestring."\n";
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