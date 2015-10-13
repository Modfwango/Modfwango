<?php
  class __CLASSNAME__ {
    public $name = "ProcessStoppedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("processStoppedEvent", $this);
      return true;
    }
  }
?>
