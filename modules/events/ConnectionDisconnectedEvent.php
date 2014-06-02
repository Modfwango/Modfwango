<?php
  class @@CLASSNAME@@ {
    public $name = "ConnectionDisconnectedEvent";

    public function preprocessEvent($name, $registrations, $connection, $data) {
      return true;
    }

    public function isInstantiated() {
      EventHandling::createEvent("connectionDisconnectedEvent", $this,
        "preprocessEvent");
      return true;
    }
  }
?>
