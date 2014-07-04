<?php
  class @@CLASSNAME@@ {
    public $name = "ConnectionLoopEndEvent";

    public function isInstantiated() {
      EventHandling::createEvent("connectionLoopEndEvent", $this);
      return true;
    }
  }
?>
