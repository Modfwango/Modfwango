<?php
  class __CLASSNAME__ {
    public $name = "ProcessStartedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("processStartedEvent", $this);
      return true;
    }
  }
?>
