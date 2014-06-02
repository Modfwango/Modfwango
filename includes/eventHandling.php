<?php
  class EventHandling {
    private static $events = array();

    public static function createEvent($name, $module, $callback) {
      // Make sure the callback exists for processing data received.
      if (method_exists($module, $callback) && !isset(self::$events[$name])) {
        // Add the event to the pool of events.
        Logger::debug("Event '".$name."' with callback '".$callback.
          "' created.");
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

    public static function getEvents() {
      // Return an array of all events.
      return self::$events;
    }

    public static function receiveData($connection, $data) {
      // Iterate through each event.
      foreach (self::$events as $key => $event) {
        // Make sure that the event has at least one registration before wasting
        // compute time on it.
        if (count($event[2]) > 0) {
          // Allow the event to preprocess the data received to determine
          // whether or not it should be triggered.
          Logger::debug("Event '".$key."' is being preprocessed.");
          $event[0]->$event[1]($key, array($event[2], $event[3]), $connection,
            trim($data));
          Logger::debug("Event '".$key."' has been preprocessed.");
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
        $callback, $data = null) {
      // Make sure the event and the module's callback exist.
      if (isset(self::$events[$name]) && method_exists($module, $callback)) {
        Logger::debug("Module '".$module->name.
          "' registered as an event preprocessor for '".$name."'");
        // Add the callback to the pool of this event's preprocessors.
        self::$events[$name][3][] = array($module, $callback, $data);
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
          if ($name != "connectionLoopEndEvent") {
            Logger::debug("Event '".$name."' has been triggered for '".
              $registration[0]->name."'");
          }
          // Call the specified callback with specified parameters.
          return $registration[0]->$registration[1]($name, $data);
        }
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

    public static function unregisterModule($module) {
      // Iterate through each event.
      foreach (self::$events as $key => $event) {
        // Unregister the requested module.
        self::unregisterForEvent($key, $module);
      }
      return true;
    }
  }
?>
