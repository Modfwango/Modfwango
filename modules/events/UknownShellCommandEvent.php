<?php
  class __CLASSNAME__ {
    public $name = "UnknownShellCommandEvent";

    public function isInstantiated() {
      EventHandling::createEvent("unknownShellCommandEvent", $this);
      return true;
    }
  }
?>
