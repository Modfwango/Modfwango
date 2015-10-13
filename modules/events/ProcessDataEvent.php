<?php
  class __CLASSNAME__ {
    public $name = "ProcessDataEvent";

    public function isInstantiated() {
      EventHandling::createEvent("processDataEvent", $this);
      return true;
    }
  }
?>
