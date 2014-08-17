<?php
  class __CLASSNAME__ {
    public $name = "ConnectionDisconnectedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("connectionDisconnectedEvent", $this);
      return true;
    }
  }
?>
