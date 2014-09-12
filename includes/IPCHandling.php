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
            $connection->connect();
            $connection->send(json_encode($module->$method($data)));
            $connection->disconnect();
          }
        }
        Logger::info("Finished dispatch.");
        die();
      }
    }
  }
?>
