<?php
	class ModuleManagement {
		private static $modules = array();
		
		public static function isLoaded($name) {
			foreach (self::$modules as $module) {
				if (strtolower($module->name) == strtolower($name)) {
					return true;
				}
			}
			return false;
		}
		
		public static function getModuleByName($name) {
			foreach (self::$modules as $module) {
				if (strtolower($module->name) == strtolower($name)) {
					return $module;
				}
			}
			return false;
		}
		
		public static function loadModule($name, $suppressNotice = false) {
			if ($suppressNotice == false) {
				echo "Loading module \"".$name."...\"\n";
			}
			
			if (!self::isLoaded(basename($name)) && is_readable(__PROJECTROOT__."/includes/modules/".$name.".php")) {
				$classname = basename($name).time().mt_rand();
				$eval = str_ireplace("@@CLASSNAME@@", $classname, substr(trim(file_get_contents(__PROJECTROOT__."/includes/modules/".$name.".php")), 5, -2));
				eval($eval);
				$module = new $classname();
				if (is_object($module) && method_exists($module, "isInstantiated") && $module->isInstantiated()) {
					self::$modules[] = $module;
					return true;
				}
			}
			return false;
		}
		
		public static function reloadModule($name) {
			echo "Reloading module \"".$name."...\"\n";
			if (self::isLoaded(basename($name))) {
				if (self::unloadModule(basename($name), true)) {
					return self::loadModule($name, true);
				}
			}
			return false;
		}
		
		public static function unloadModule($name, $suppressNotice = false) {
			if ($suppressNotice == false) {
				echo "Unloading module \"".$name."...\"\n";
			}
			
			if (self::isLoaded(basename($name))) {
				foreach (self::$modules as $key => $module) {
					if (strtolower($module->name) == strtolower(basename($name))) {
						EventHandling::unregisterModule($module);
						unset(self::$modules[$key]);
						return true;
					}
				}
			}
			return false;
		}
	}
?>
