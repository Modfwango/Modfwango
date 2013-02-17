<?php
	class ConfigParser {
		public static function getAssoc($input) {
			$return = array();
			if (is_array($input)) {
				foreach ($input as $item) {
					$return[$item["name"]] = $item["value"];
				}
			}
			return $return;
		}
		
		private static function parseFile($file) {
			$lines = explode("\n", trim(file_get_contents($file)));
			$return = array();
			foreach ($lines as &$line) {
				$line = trim($line);
				if (stristr($line, "=")) {
					$item = explode("=", $line);
					if (count($item) == 2) {
						$return[] = array(
							"name" => trim($item[0]),
							"value" => trim($item[1])
						);
					}
				}
			}
			unset($lines);
			return $return;
		}
		
		public static function parseFiles($input) {
			if (is_array($input)) {
				foreach ($input as $key => $item) {
					$input[$key] = self::parseFile($item);
				}
			}
			else {
				$input = self::parseFile($input);
			}
			return $input;
		}
	}
?>