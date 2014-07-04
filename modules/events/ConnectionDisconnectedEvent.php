<?php
  class @@CLASSNAME@@ {
    public $name = "ConnectionDisconnectedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("connectionDisconnectedEvent", $this);
      return true;
    }
  }
?>
