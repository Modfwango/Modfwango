<?php
  class @@CLASSNAME@@ {
    public $name = "ConnectionConnectedEvent";

    public function preprocessEvent($name, $registrations, $connection, $data) {
      return true;
    }

    public function isInstantiated() {
      EventHandling::createEvent("connectionConnectedEvent", $this,
        "preprocessEvent");
      return true;
    }
  }
?>
