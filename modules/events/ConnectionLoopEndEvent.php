<?php
  class __CLASSNAME__ {
    public $name = "ConnectionLoopEndEvent";

    public function isInstantiated() {
      EventHandling::createEvent("connectionLoopEndEvent", $this);
      return true;
    }
  }
?>
