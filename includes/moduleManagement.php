<?php
  class ModuleManagement {
    private static $modules = array();
    private static $waitingList = array();

    private static function checkDependencies($module) {
      // Check to see if the module has dependencies.
      if (isset($module->depend) && is_array($module->depend)
          && count($module->depend) > 0) {
        $depend = $module->depend;
        // Unset dependencies that are already loaded.
        foreach ($depend as $key => $d) {
          if (self::isLoaded($d)) {
            unset($depend[$key]);
          }
        }
        // If there are no more dependencies, allow the module to load.
        if (count($depend) == 0) {
          return true;
        }
      }
      else {
        return true;
      }
      // We need to wait to load this module.
      return false;
    }

    private static function determineModuleRoot($name) {
      // Prefer Modfwango core modules over custom modules.
      if (is_readable(__MODFWANGOROOT__."/modules/".$name.".php")) {
        return __MODFWANGOROOT__;
      }
      // If a core module doesn't exist with said name, check for one in the
      // project scope.
      if (is_readable(__PROJECTROOT__."/modules/".$name.".php")) {
        return __PROJECTROOT__;
      }
      return false;
    }

    public static function isLoaded($name) {
      // Iterate through all loaded modules.
      foreach (self::$modules as $module) {
        // If the names match, the module is loaded.
        if (strtolower($module->name) == strtolower($name)) {
          return true;
        }
      }
      // Module with said name is not loaded.
      return false;
    }

    public static function getLoadedModules() {
      // Return an array of all loaded modules.
      return self::$modules;
    }

    public static function getLoadedModuleNames() {
      // Assign an empty array to a temporary variable for name storage.
      $list = array();
      // Iterate through all loaded modules.
      foreach (self::$modules as $module) {
        // Add the name of each module to the list.
        $list[] = $module->name;
      }
      // Return the list of module names.
      return $list;
    }

    public static function getModuleByName($name) {
      // Iterate through all loaded modules.
      foreach (self::$modules as $module) {
        // If the names match, return the module.
        if (strtolower($module->name) == strtolower($name)) {
          return $module;
        }
      }
      // Module with said name is not loaded.
      return false;
    }

    public static function loadModule($name, $suppressNotice = false) {
      // Check to see if this module needs to be loaded quietly.
      if ($suppressNotice == false) {
        Logger::debug("Loading module \"".$name."...\"");
      }

      // Set the root path of the module in a temporary variable.
      $root = self::determineModuleRoot($name);
      // Make sure a module with said name isn't already loaded and the path is
      // valid.
      if (!self::isLoaded(basename($name)) && $root != false) {
        // Generate a random class name to allow for reloadable modules.
        $classname = basename($name).time().mt_rand();
        // Setup the eval string, replacing placeholders with values.
        $eval = str_ireplace("@@CLASSNAME@@", $classname,
          substr(trim(file_get_contents($root."/modules/".
          $name.".php")), 5, -2));
        // Eval the string to create the class.
        eval($eval);
        // Make sure something didn't go wrong.
        if (class_exists($classname)) {
          // Instantiate the newly created class into a temporary variable.
          $module = new $classname();
          // Make sure the variable is an object and it conforms to the module
          // API required to load modules.
          if (is_object($module) && method_exists($module, "isInstantiated")) {
            // Make sure no dependencies are needed.
            if (self::checkDependencies($module)) {
              // Run setup function to allow the module to prepare itself.
              if ($module->isInstantiated()) {
                // Add the module to the list of loaded modules.
                self::$modules[] = $module;
                Logger::info("Loaded module \"".$name."\"");
                // Iterate through the waiting list to check if loading this
                // module resolved any other dependencies.
                $lastCount = -1;
                while (count(self::$waitingList) !== $lastCount) {
                  $lastCount = count(self::$waitingList);
                  foreach (self::$waitingList as $key => $item) {
                    if (self::checkDependencies($item[1])
                        && $item[1]->isInstantiated()) {
                      // Loading the previous module allows this module to be
                      // loaded.
                      self::$modules[] = $item[1];
                      Logger::info("Loaded module \"".$item[0]."\"");
                      // Remove newly loaded module from the waiting list.
                      unset(self::$waitingList[$key]);
                    }
                  }
                }
                return true;
              }
            }
            else {
              // Add the module to the waiting list due to unloaded
              // dependencies.
              self::$waitingList[] = array($name, $module);
              Logger::info("Deferring load of module \"".$name."\"");
              return null;
            }
          }
          else {
            // The module didn't create a valid object, or doesn't adhere to the
            // module API.
            Logger::info("Unable to load module \"".$name."\"");
            Logger::debug("Class \"".$classname.
              "\" does not contain method \"isInstantiated()\" or it returned ".
              "false.  Failing quietly.");
          }
        }
        else {
          // A class was not created by eval().
          Logger::info("Unable to load module \"".$name.".\"");
          Logger::debug("Class \"".$classname.
            "\" was not created by eval().  Failing quietly.");
        }
      }
      return false;
    }

    public static function reloadModule($name) {
      Logger::debug("Reloading module \"".$name."...\"");
      // Make sure the module is actually loaded.
      if (self::isLoaded(basename($name))) {
        // Check to see if the module is reloadable.
        if (!method_exists(self::getModuleByName(basename($name)),
            "isReloadable") ||
            self::getModuleByName(basename($name))->isReloadable() == true) {
          // Unload the module.
          if (self::unloadModule($name, true)) {
            // Load the module.
            if(self::loadModule($name, true)) {
              Logger::info("Reloaded module \"".$name."\"");
              return true;
            }
          }
        }
      }
      // Something went wrong!
      return false;
    }

    public static function unloadModule($name, $suppressNotice = false) {
      // Check to see if this module needs to be loaded quietly.
      if ($suppressNotice == false) {
        Logger::debug("Unloading module \"".$name."...\"");
      }

      // Make sure the module is actually loaded.
      if (self::isLoaded(basename($name))) {
        // Check to see if the module is unloadable.
        if (!method_exists(self::getModuleByName(basename($name)),
            "isUnloadable") ||
            self::getModuleByName(basename($name))->isUnloadable() == true) {
          // Iterate through each module.
          foreach (self::$modules as $key => $module) {
            // If the names match, unload the module.
            if (strtolower($module->name) == strtolower(basename($name))) {
              // Iterate through all modules.
              foreach (self::$modules as $m) {
                // Check if they depend on anything.
                if (isset($m->depend) && is_array($m->depend)
                    && count($m->depend) > 0) {
                  foreach ($m->depend as $d) {
                    // Do they depend on this module?
                    if (strtolower($d) == strtolower(basename($name))) {
                      // Unload it as well!
                      if (!self::unloadModule($m->name)) {
                        // If we can't unload this module, we can't unload the
                        // original module.
                        return false;
                      }
                      break;
                    }
                  }
                }
              }
              // Unregister all events associated with this module.
              EventHandling::unregisterModule($module);
              unset(self::$modules[$key]);
              Logger::info("Unloaded module \"".$name."\"");
              return true;
            }
          }
        }
      }
      // Something went wrong!
      return false;
    }
  }
?>
