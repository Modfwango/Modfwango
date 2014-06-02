<?php
  class Main {
    public function __construct($argv) {
      // Verify that the bot can run in the provided environment.
      $this->verifyEnvironment();

      if (isset($argv[1])) {
        // Ensure that any non-default user input is converted to a boolean.
        $debug = (bool)$argv[1];
      }
      else {
        $debug = false;
      }

      // Activate full error reporting.
      $this->setErrorReporting();

      // Setup required constants for operation and load required classes.
      $this->prepareEnvironment($debug);

      // Load requested modules.
      $this->loadModules();

      // Discover sockets located in conf/listen.conf.
      $this->discoverSockets();

      // Start the main loop.
      $this->loop();

      // Return a false value if the loop fails.
      return false;
    }

    private function discoverSockets() {
      // Load the listen configuration.
      $listen = trim(file_get_contents(__PROJECTROOT__."/conf/listen.conf"));
      $listen = explode("\n", $listen);
      // Iterate through each line.
      foreach ($listen as $sock) {
        // Make sure there are no stray line endings.
        $sock = trim($sock);
        // Make sure the line has a non-null value and a comma.
        if (strlen($sock) > 0 && strstr($sock, ",")) {
          // Separate bind address from bind port.
          $sock = explode(",", $sock);
          // Make sure we have the correct amount of parameters.
          if (count($sock) == 2) {
            // Attempt to create a socket.
            $sock = new Socket($sock[0], $sock[1]);
            if ($sock != false) {
              // Add it to the socket management class.
              SocketManagement::newSocket($sock);
            }
            else {
              // Couldn't bind!
              Logger::debug("Could not bind to address.");
            }
          }
        }
      }
    }

    private function loadModules() {
      // Load modules in requested order in modfwango conf/modules.conf.
      foreach (explode("\n",
          trim(file_get_contents(__MODFWANGOROOT__."/conf/modules.conf")))
          as $module) {
        $module = trim($module);
        if (strlen($module) > 0) {
          ModuleManagement::loadModule($module);
        }
      }

      // Load modules in requested order in project conf/modules.conf.
      foreach (explode("\n",
          trim(file_get_contents(__PROJECTROOT__."/conf/modules.conf")))
          as $module) {
        $module = trim($module);
        if (strlen($module) > 0) {
          ModuleManagement::loadModule($module);
        }
      }
    }

    private function loop() {
      // Infinitely loop.
      while (true) {
        // Iterate through each socket.
        foreach (SocketManagement::getSockets() as $socket) {
          // Attempt to accept new connections.
          $socket->accept();
        }

        // Iterate through each connection.
        foreach (ConnectionManagement::getConnections() as $connection) {
          // Fetch any received data.
          $data = $connection->getData();
          if ($data != false) {
            // Pass the connection and associated data to the event handler.
            EventHandling::receiveData($connection, $data);
          }
          // Sleep for a small amount of time to prevent high CPU usage.
          usleep(10000);
        }

        // Iterate through each event to find the connectionLoopEndEvent event.
        foreach (EventHandling::getEvents() as $key => $event) {
          if ($key == "connectionLoopEndEvent") {
            foreach ($event[2] as $id => $registration) {
              // Trigger the connectionLoopEndEvent event for each registered
              // module.
              EventHandling::triggerEvent("connectionLoopEndEvent", $id);
            }
          }
        }
      }
    }

    private function prepareEnvironment($debug) {
      // Define the root of the Modfwango library folder.
      define("__MODFWANGOROOT__", dirname(__FILE__));

      // Change current working directory to project root.
      chdir(__PROJECTROOT__);

      // Define start timestamp.
      define("__STARTTIME__", time());

      // Define the debug constant to allow the logger determine the correct
      // output type.
      define("__DEBUG__", $debug);

      // Load the connection related classes.
      require_once(__MODFWANGOROOT__."/includes/connection.php");
      require_once(__MODFWANGOROOT__."/includes/connectionManagement.php");

      // Load the event handler.
      require_once(__MODFWANGOROOT__."/includes/eventHandling.php");

      // Load the logger.
      require_once(__MODFWANGOROOT__."/includes/logger.php");

      // Load the module management class.
      require_once(__MODFWANGOROOT__."/includes/moduleManagement.php");

      // Load the storage handling class.
      require_once(__MODFWANGOROOT__."/includes/storageHandling.php");
    }

    private function setErrorReporting() {
      error_reporting(E_ALL);
      ini_set("display_errors", 1);
    }

    private function verifyEnvironment() {
      // Verify that the current directory structure is named safely.
      if (!preg_match("/^[a-zA-Z0-9\\/.\\-]+$/", dirname(__FILE__))) {
        die("The full path to this file must match this regular expression:\n^".
          "[a-zA-Z0-9\\/.\\-]+$\n");
      }

      // Verify that the launcher script has setup the project root constant.
      if (!defined("__PROJECTROOT__")) {
        die("__PROJECTROOT__ hasn't been defined by a launcher script.\n");
      }
    }
  }

  // Instantiate the bot to get things moving.
  $bot = new Main($argv);
?>
