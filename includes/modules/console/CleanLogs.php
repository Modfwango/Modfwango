<?php
	class @@CLASSNAME@@ {
		public $name = "CleanLogs";
		
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
		
		public function receiveChannelJoin($name, $data) {
			$connection = $data[0];
			$source = $data[1];
			$target = $data[2];
			
			if (substr($target, 0, 1) == ":") {
				$target = substr($target, 1);
			}
			
			echo "[".$connection->getNetworkName()." / ".$target."] * ".$source[0]."(".$source[1]."@".$source[2].") Join\n";
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
		
		public function isInstantiated() {
			EventHandling::registerForEvent("channelMessageEvent", $this, "receiveChannelMessage");
			EventHandling::registerForEvent("channelJoinEvent", $this, "receiveChannelJoin");
			EventHandling::registerForEvent("channelPartEvent", $this, "receiveChannelPart");
			EventHandling::registerForEvent("channelQuitEvent", $this, "receiveChannelQuit");
			return true;
		}
	}
?>