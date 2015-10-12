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
        foreach ($event[2] as $id => $registration)
          // Trigger registrations with matching command preference
          if (strtolower($registration[2]) == strtolower($cmd))
            EventHandling::triggerEvent($name, $id, array($cmd, $args));
    }

    public function isInstantiated() {
      EventHandling::createEvent("shellCommandEvent", $this);
      return true;
    }
  }
?>
