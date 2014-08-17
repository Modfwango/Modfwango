<?php
  class __CLASSNAME__ {
    public $name = "ConnectionCreatedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("connectionCreatedEvent", $this);
      return true;
    }
  }
?>
