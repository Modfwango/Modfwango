<?php
  class IPCHandling {
    private static $threads = array();

    public static function dispatch($module, $method, $callback, $data = null) {
      $uuid = md5(rand().time());
      self::$threads[$uuid] = array($module, $callback);
      $pid = pcntl_fork();
      if ($pid == -1) {
        Logger::info("Couldn't fork. Exiting...");
        die();
      } else if ($pid) {
        Logger::debug("Dispatched thread for UUID ".$uuid);
        return $uuid;
      } else {
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
            sleep(1);
            $connection->disconnect();
            Logger::debug("Finished dispatch for UUID ".$uuid);
            break;
          }
        }
        // Make sure the child dies after it's done processing data.
        die();
      }
    }

    public static function receiveData($connection, $data) {
      Logger::debug(var_export($data, true));
      $data = @json_decode($data, true);
      if (is_array($data) && isset($threads[$data[0]])) {
        $threads[$data[0]][0]->$threads[$data[0]][1]($data[0], $data[1]);
        unset($threads[$data[0]]);
      }
      $connection->disconnect();
    }
  }
?>
