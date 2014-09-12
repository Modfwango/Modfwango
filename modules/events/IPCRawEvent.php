<?php
  class __CLASSNAME__ {
    public $name = "IPCRawEvent";

    public function preprocessEvent($name, $registrations, $connection, $data) {
      if ($connection->getIPC() == true) {
        $ex = explode(" ", $data);

        // Iterate through each registration.
        foreach ($registrations as $id => $registration) {
          // Trigger the event for a certain registration.
          EventHandling::triggerEvent($name, $id, array($connection, $data, $ex));
        }
      }
    }

    public function isInstantiated() {
      // Create an event for raw data.
      EventHandling::createEvent("IPCRawEvent", $this, "preprocessEvent");
      return true;
    }
  }
?>
