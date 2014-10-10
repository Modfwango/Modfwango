<?php
  class EventHandling {
    private static $events = array();

    public static function createEvent($name, $module, $callback = null) {
      // Make sure the callback exists for processing data received.
      if (is_object($module) && !isset(self::$events[$name])) {
        // Add the event to the pool of events.
        Logger::debug("Event '".$name."'".((is_string($callback) && strlen(
          $callback) > 0) ? " with callback '".$callback."'" : null).
          " created.");
        self::$events[$name] = array($module, $callback, array(), array());
        return true;
      }
      return false;
    }

    public static function destroyEvent($name) {
      // Check to see if said event exists.
      if (isset(self::$events[$name])) {
        // Remove the event, including its registrations.
        Logger::debug("Event '".$name."' destroyed.");
        unset(self::$events[$name]);
        return true;
      }
      return false;
    }

    public static function getEventByName($name) {
      if (isset(self::$events[$name])) {
        return self::$events[$name];
      }
      return false;
    }

    public static function getEvents() {
      // Return an array of all events.
      return self::$events;
    }

    public static function receiveData($connection, $data) {
      // Iterate through each event.
      foreach (self::$events as $key => $event) {
        // Make sure that the event has a data preprocessor and at least one
        // registration before wasting compute time on it.
        if (is_string($event[1]) && strlen($event[1]) > 0 &&
            count($event[2]) > 0) {
          // Allow the event to preprocess the data received to determine
          // whether or not it should be triggered.
          Logger::stack("Entering module: ".$event[0]->name."::".$event[1]);
          $event[0]->$event[1]($key, $event[2], $connection, trim($data));
          Logger::stack("Left module: ".$event[0]->name."::".$event[1]);
        }
      }
      return true;
    }

    public static function registerForEvent($name, $module, $callback,
        $data = null) {
      // Make sure the event and the module's callback exist.
      if (isset(self::$events[$name]) && method_exists($module, $callback)) {
        Logger::debug("Module '".$module->name."' registered for event '".
          $name."'");
        // Add the callback to the pool of this event's registrations.
        self::$events[$name][2][] = array($module, $callback, $data);
        return true;
      }
      return false;
    }

    public static function registerAsEventPreprocessor($name, $module,
        $callback) {
      // Make sure the event and the module's callback exist.
      if (isset(self::$events[$name]) && method_exists($module, $callback)) {
        Logger::debug("Module '".$module->name.
          "' registered as an event preprocessor for '".$name."'");
        // Add the callback to the pool of this event's preprocessors.
        self::$events[$name][3][] = array($module, $callback);
        return true;
      }
      return false;
    }

    public static function triggerEvent($name, $id, $data = null) {
      // Make sure the specific registration exists.
      if (isset(self::$events[$name][2][$id])) {
        $registration = self::$events[$name][2][$id];
        // Make sure the registration callback exists.
        if (method_exists($registration[0], $registration[1])) {
          // Loop through each preprocessor for this event.
          foreach (self::$events[$name][3] as $preprocessor) {
            // Make sure the preprocessor callback exists.
            if (method_exists($preprocessor[0], $preprocessor[1])) {
              // Fetch the result of the preprocessor callback.
              Logger::stack("Entering module: ".$preprocessor[0]->name."::".
                $preprocessor[1]);
              $result = $preprocessor[0]->$preprocessor[1]($name, $id, $data);
              Logger::stack("Left module: ".$preprocessor[0]->name."::".
                $preprocessor[1]);
              // Make sure the result conforms to the result protocol.
              if (is_array($result)) {
                if ($result[0] === false) {
                  // If the result is false, prevent the event from triggering.
                  Logger::debug("Event '".$name."' has been cancelled for '".
                    $registration[0]->name."'");
                  return false;
                }
                elseif ($result[0] === null && isset($result[1])) {
                  // If the result is null, replace the data variable.
                  $data = $result[1];
                }
              }
            }
          }
          if ($name != "connectionLoopEndEvent") {
            Logger::debug("Event '".$name."' has been triggered for '".
              $registration[0]->name."'");
          }
          // Call the specified callback with specified parameters.
          Logger::stack("Entering module: ".$registration[0]->name."::".
            $registration[1]);
          return $registration[0]->$registration[1]($name, $data);
          Logger::stack("Left module: ".$registration[0]->name."::".
            $registration[1]);
        }
      }
      return false;
    }

    public static function unregisterEvent($module) {
      if (is_object($module) && isset($module->name)) {
        // If the parameter is a module, remove any event created by it.
        foreach (self::$events as $key => $event) {
          if ($event[0]->name == $module->name) {
            self::destroyEvent($key);
          }
        }
        return true;
      }
      return false;
    }

    public static function unregisterForEvent($name, $module) {
      // Make sure the event exists.
      if (isset(self::$events[$name])) {
        // Iterate through each registration.
        foreach (self::$events[$name][2] as $key => $registration) {
          // If the module's name matches the registration's module's name,
          // cancel the registration.
          if ($registration[0]->name == $module->name) {
            Logger::debug("Module '".$module->name."' unregistered for event '".
              $name."'");
            // Unset the registration by ID.
            unset(self::$events[$name][2][$key]);
            return true;
          }
        }
      }
      return false;
    }

    public static function unregisterPreprocessorForEvent($name, $module) {
      // Make sure the event exists.
      if (isset(self::$events[$name])) {
        // Iterate through each registration.
        foreach (self::$events[$name][3] as $key => $preprocessor) {
          // If the module's name matches the preprocessor's module's name,
          // cancel the preprocessor.
          if ($preprocessor[0]->name == $module->name) {
            Logger::debug("Module '".$module->name."' unregistered ".
              "preprocessor for event '".$name."'");
            // Unset the registration by ID.
            unset(self::$events[$name][3][$key]);
            return true;
          }
        }
      }
      return false;
    }

    public static function unregisterModule($module) {
      // Remove any event made by this module.
      self::unregisterEvent($module);
      // Iterate through each event.
      foreach (self::$events as $key => $event) {
        // Unregister the requested module.
        self::unregisterForEvent($key, $module);
        self::unregisterPreprocessorForEvent($key, $module);
      }
      return true;
    }
  }
?>
