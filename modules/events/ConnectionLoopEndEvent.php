<?php
  class @@CLASSNAME@@ {
    public $name = "ConnectionLoopEndEvent";

    public function preprocessEvent($name, $registrations, $connection, $data) {
      return true;
    }

    public function isInstantiated() {
      EventHandling::createEvent("connectionLoopEndEvent", $this,
        "preprocessEvent");
      return true;
    }
  }
?>
