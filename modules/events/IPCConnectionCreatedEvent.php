<?php
  class __CLASSNAME__ {
    public $name = "IPCConnectionCreatedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("IPCConnectionCreatedEvent", $this);
      return true;
    }
  }
?>
