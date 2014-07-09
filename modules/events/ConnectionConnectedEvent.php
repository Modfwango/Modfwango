<?php
  class @@CLASSNAME@@ {
    public $name = "ConnectionConnectedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("connectionConnectedEvent", $this);
      return true;
    }
  }
?>
