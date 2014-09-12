<?php
  class IPCHandling {
    public static function dispatch($module, $method, $data = null) {
      $uuid = md5(rand().time());
      $pid = pcntl_fork();
      if ($pid == -1) {
        Logger::info("Couldn't fork. Exiting...");
        die();
      } else if ($pid) {
        //parent
        Logger::debug("Dispatched thread for UUID ".$uuid);
        return $uuid;
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
            $connection->connect();
            $connection->send(json_encode(array(
              $uuid,
              $module->$method($data)
            )));
            $connection->disconnect();
            Logger::debug("Finished dispatch for UUID ".$uuid);
            die();
          }
        }
        // Make sure the child dies if there is no IPC socket.
        die();
      }
    }
  }
?>
