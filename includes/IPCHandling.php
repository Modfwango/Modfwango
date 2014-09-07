<?php
  class IPCHandling {
    public function receiveData($connection, $data) {
      Logger::info($connection->getConnectionString()." ".$data);
    }
  }
?>
