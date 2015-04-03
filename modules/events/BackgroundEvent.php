<?php
  class __CLASSNAME__ {
    public $name = "BackgroundEvent";

    public function isInstantiated() {
      EventHandling::createEvent("backgroundEvent", $this);
      return true;
    }
  }
?>
