<?php
  class @@CLASSNAME@@ {
    public $depend = array("Timer");
    public $name = "ConnectionCreatedEvent";

    public function checkConnection($connection) {
      if ($connection->send(chr(6), false)) {
        $this->setTimer($connection);
      }
    }

    public function connectionCreated($name, $data) {
      $this->setTimer($data);
    }

    public function preprocessEvent($name, $registrations, $connection, $data) {
      return true;
    }

    public function setTimer($connection) {
      ModuleManagement::getModuleByName("Timer")->newTimer(1, $this,
        "checkConnection", $connection);
    }

    public function isInstantiated() {
      EventHandling::createEvent("connectionCreatedEvent", $this,
        "preprocessEvent");
      EventHandling::registerForEvent("connectionCreatedEvent", $this,
        "connectionCreated");
      return true;
    }
  }
?>
