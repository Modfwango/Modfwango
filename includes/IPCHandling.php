<?php
  class IPCHandling {
    public static function dispatch($module, $method, $callback, $data = null) {
      $pid = pcntl_fork();
      if ($pid == -1) {
        die('could not fork');
      } else if ($pid) {
        //parent
        return $pid;
      } else {
        //child
        foreach (SocketManagement::getSockets() as $socket) {
          if ($socket->getIPC() == true) {
            $connection = new Connection("0", array(
              $socket->getHost(),
              $socket->getPort(),
              $socket->getSSL(),
              array()
            ), true);
            $connection->send(json_encode($module->$method($data)));
            $connection->disconnect();
          }
        }
        die();
      }
    }

    public static function receiveData($connection, $data) {
      // Get the IPCRawEvent event.
      $event = EventHandling::getEventByName("IPCRawEvent");
      if ($event != false) {
        foreach ($event[2] as $id => $registration) {
          // Trigger the IPCRawEvent event for each registered
          // module.
          EventHandling::triggerEvent("IPCRawEvent", $id,
            array($connection, $data));
        }
      }
    }
  }
?>
