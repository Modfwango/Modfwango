<?php
  class __CLASSNAME__ {
    public $depend = array("ConnectionLoopEndEvent");
    public $name = "Timer";
    private $timers = array();

    public function connectionLoopEnd() {
      // Iterate through each timer
      foreach ($this->timers as $id => $timer) {
        // Check to see if the timer is valid and ready to fire
        if ($timer != null && $timer["runtime"] <= microtime(true)) {
          $class = $timer["class"];
          $callback = $timer["callback"];

          // Check to see if the class has a name
          if (isset($class->name)) {
            Logger::debug("Processing timer for '".$class->name."->".
              $callback."()'");
            // Load the module by name
            $mod = ModuleManagement::getModuleByName($class->name);
            // Make sure the module is an object
            if (is_object($mod)) {
              // Make sure both classnames match
              if (get_class($mod) == get_class($class)) {
                Logger::stack("Entering module: ".$class->name."::".$callback);
                $class->$callback($timer["params"]);
                Logger::stack("Left module: ".$class->name."::".$callback);
              }
              else {
                // Attempted to use an old module
                Logger::info("Kept from resurrecting potentially old (unloaded".
                  ") code.  Module's class name does not match original. (".
                  get_class($mod)." -> ".get_class($class).")");
              }
            }
          }
          else {
            if (is_object($class)) {
              Logger::stack("Entering module: ".$class->name."::".$callback);
              $class->$callback($timer["params"]);
              Logger::stack("Left module: ".$class->name."::".$callback);
            }
          }

          // Invalidate the timer
          $this->timers[$id] = null;
        }
      }
      return true;
    }

    public function newTimer($dtime, $class, $callback, $params) {
      // Make sure the parameters are of the correct type and that the callback
      // is valid
      if (is_numeric($dtime) && $dtime > -1 && is_object($class)
          && method_exists($class, $callback)) {
        // Find an unused index
        $i = 1;
        while (isset($this->timers[$i])) {
          $i++;
        }

        // Setup the timer and add it to the timer list
        $this->timers[$i] = array(
          "runtime" => (microtime(true) + $dtime),
          "class" => $class,
          "callback" => $callback,
          "params" => $params
        );

        // Check to see if the class has a name
        if (isset($class->name)) {
          Logger::debug("Timer created for '".$class->name."->".$callback.
            "()' for ".$dtime." seconds.");
        }
        else {
          Logger::debug("Timer created for '".$callback."()' for ".$dtime.
            " seconds.");
        }

        // Return the index of the timer
        return $i;
      }
      return false;
    }

    public function isInstantiated() {
      // Register for connectionLoopEndEvent to obtain an opportunity at the end
      // of every loop to check timers
      EventHandling::registerForEvent("connectionLoopEndEvent", $this,
        "connectionLoopEnd");
      return true;
    }
  }
?>
