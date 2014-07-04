<?php
  class @@CLASSNAME@@ {
    public $name = "ConnectionCreatedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("connectionCreatedEvent", $this);
      return true;
    }
  }
?>
