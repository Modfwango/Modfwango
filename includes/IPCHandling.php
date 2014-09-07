<?php
  class IPCHandling {
    public static function receiveData($connection, $data) {
      Logger::info(var_export($data, true));
    }
  }
?>
