<?php
  class __CLASSNAME__ {
    public $name = "ShellCommandEvent";

    public function receiveCommand($cmd, $args) {
      // Fetch this module's event
      $name  = "shellCommandEvent";
      $event = EventHandling::getEventByName($name);
      // Ensure the event is valid
      if (is_array($event) && is_array($event[2]))
        // Iterate over each registration to verify validity
        foreach ($event[2] as $id => $registration) {
          // Prepare the command filter array for processing
          if (is_array($registration[2]))
            $filter = array_map('strtolower', array_map('trim',
              $registration[2]));
          else
            $filter = array(strtolower($registration[2]));
          // Trigger registrations with matching command preference (null
          // accepts any command)
          if (in_array(strtolower($cmd), $filter) || $filter == null)
            EventHandling::triggerEvent($name, $id, array($cmd, $args));
        }
    }

    public function isInstantiated() {
      EventHandling::createEvent("shellCommandEvent", $this);
      return true;
    }
  }
?>
