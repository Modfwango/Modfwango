<?php
  class __CLASSNAME__ {
    public $name = "ConnectionConnectedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("connectionConnectedEvent", $this);
      return true;
    }
  }
?>
