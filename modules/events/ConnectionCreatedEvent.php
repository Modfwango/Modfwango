<?php
  class @@CLASSNAME@@ {
    public $name = "ConnectionCreatedEvent";

    public function preprocessEvent($name, $registrations, $connection, $data) {
      return true;
    }

    public function isInstantiated() {
      EventHandling::createEvent("connectionCreatedEvent", $this,
        "preprocessEvent");
      return true;
    }
  }
?>
