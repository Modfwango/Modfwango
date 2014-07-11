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

      // Brag about versions and junk.
      $this->brag();

      // Load requested modules.
      $this->loadModules();

      // Discover sockets located in conf/listen.conf.
      $this->discoverSockets();

      // Discover connections located in conf/connections/.
      $this->discoverConnections();

      // Initiate all loaded connections.
      $this->activateConnections();
    }

    private function activateConnections() {
      // Iterate through the list of defined connections.
      foreach (ConnectionManagement::getConnections() as $connection) {
        // Connect.
        $connection->connect();
      }
    }

    private function brag() {
      Logger::info("Welcome to Modfwango!");
      Logger::info("You're running Modfwango v".
        __MODFWANGOVERSION__.".");
      // Check for updates to Modfwango.
      $version = "1.00";
      $contents = @explode("\n", @file_get_contents("https://raw.githubusercon".
        "tent.com/ClayFreeman/Modfwango/master/CHANGELOG.md", 0,
        stream_context_create(array('http' => array('timeout' => 1)))));
      if (is_array($contents)) {
        foreach ($contents as $line) {
          if (preg_match("/^[#]{6} (.*)$/i", trim($line), $matches)) {
            $v = explode(" ", $matches[1]);
            $v = trim($v[0]);
            if (version_compare(__MODFWANGOVERSION__, $v, "<")) {
              Logger::info("An update is available at http://modfwango.com/");
            }
            return;
          }
        }
      }
    }

    private function discoverConnections() {
      // Get a list of connection configurations.
      $connections = glob(__PROJECTROOT__."/conf/connections/*.conf");

      // Iterate through the list and load each item individually.
      foreach ($connections as $file) {
        ConnectionManagement::loadConnectionFile($file);
      }
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
            $sock = new Socket(trim($sock[0]), trim($sock[1]));
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
          $modules[] = $module;
          if (ModuleManagement::loadModule($module) === false) {
            Logger::info("Module \"".$module."\" failed to load.");
            die();
          }
        }
      }

      // Load modules in requested order in project conf/modules.conf.
      foreach (explode("\n",
          trim(file_get_contents(__PROJECTROOT__."/conf/modules.conf")))
          as $module) {
        $module = trim($module);
        if (strlen($module) > 0) {
          $modules[] = $module;
          if (ModuleManagement::loadModule($module) === false) {
            Logger::info("Module \"".$module."\" failed to load.");
            die();
          }
        }
      }

      // Make sure all requested modules were loaded.
      foreach ($modules as $module) {
        if (!ModuleManagement::isLoaded(basename($module))) {
          Logger::info("Module \"".$module."\" failed to load.");
          die();
        }
      }
    }

    public function loop() {
      // Infinitely loop.
      while (true) {
        // Iterate through each socket.
        foreach (SocketManagement::getSockets() as $socket) {
          // Attempt to accept new connections.
          $socket->accept();
        }

        // Prune dead connections.
        ConnectionManagement::pruneConnections();

        // Iterate through each connection.
        foreach (ConnectionManagement::getConnections() as $connection) {
          // Fetch any received data.
          $data = trim($connection->getData());
          if ($data != false) {
            if (stristr($data, "\n")) {
              foreach (explode("\n", $data) as $line) {
                // Pass the connection and associated data to the event handler.
                EventHandling::receiveData($connection, trim($line));
              }
            }
            else {
              // Pass the connection and associated data to the event handler.
              EventHandling::receiveData($connection, trim($data));
            }
          }
        }

        // Get the connectionLoopEndEvent event.
        $event = EventHandling::getEventByName("connectionLoopEndEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the connectionLoopEndEvent event for each registered
            // module.
            EventHandling::triggerEvent("connectionLoopEndEvent", $id);
          }
        }
        // Sleep for a small amount of time to prevent high CPU usage.
        usleep(__DELAY__);
      }
    }

    private function prepareEnvironment($debug) {
      // Define the root of the Modfwango library folder.
      define("__MODFWANGOROOT__", dirname(__FILE__));

      // Locate the latest version in CHANGELOG.md.
      $version = "1.00";
      if (file_exists(__MODFWANGOROOT__."/CHANGELOG.md")) {
        $contents = explode("\n", file_get_contents(__MODFWANGOROOT__.
          "/CHANGELOG.md"));
        foreach ($contents as $line) {
          if (preg_match("/^[#]{6} (.*)$/i", trim($line), $matches)) {
            $version = explode(" ", $matches[1]);
            $version = trim($version[0]);
            break;
          }
        }
      }

      // Define the current version of Modfwango.
      define("__MODFWANGOVERSION__", $version);

      // Change current working directory to project root.
      chdir(__PROJECTROOT__);

      // Set the default timezone.
      if (defined("__TIMEZONE__")) {
        date_default_timezone_set(__TIMEZONE__);
      }

      // Define start timestamp.
      define("__STARTTIME__", time());

      // Define the debug constant to allow the logger determine the correct
      // output type.
      define("__DEBUG__", $debug);

      // Define the time to sleep at the end of every infinite loop.
      define("__DELAY__", 10000);

      // Load the connection related classes.
      require_once(__MODFWANGOROOT__."/includes/connection.php");
      require_once(__MODFWANGOROOT__."/includes/connectionManagement.php");
      require_once(__MODFWANGOROOT__."/includes/socket.php");
      require_once(__MODFWANGOROOT__."/includes/socketManagement.php");

      // Load the event handler.
      require_once(__MODFWANGOROOT__."/includes/eventHandling.php");

      // Load the logger.
      require_once(__MODFWANGOROOT__."/includes/logger.php");

      // Make sure the launcher is up-to-date.
      if (!file_exists(__PROJECTROOT__."/main.php")
          || hash("md5", file_get_contents(__MODFWANGOROOT__."/launcher.php"))
          != hash("md5", file_get_contents(__PROJECTROOT__."/main.php"))) {
        file_put_contents(__PROJECTROOT__."/main.php", file_get_contents(
          __MODFWANGOROOT__."/launcher.php"));
        Logger::info("The launcher has been updated.");
      }

      // Load the module management class.
      require_once(__MODFWANGOROOT__."/includes/moduleManagement.php");

      // Load the storage handling class.
      require_once(__MODFWANGOROOT__."/includes/storageHandling.php");

      // Register signal handlers.
      declare(ticks = 1);
      if (function_exists("pcntl_signal")) {
        pcntl_signal(SIGINT,
          function() {
            echo "\r";
            Logger::info("Caught SIGINT; Begin shutdown procedure...");
            foreach (ConnectionManagement::getConnections() as $c) {
              $c->disconnect();
            }
            foreach (SocketManagement::getSockets() as $s) {
              $s->close();
            }
            Logger::info("Shutting down...");
            die();
          }
        );
        pcntl_signal(SIGTSTP,
          function() {
            echo "\r";
            Logger::memoryUsage();
          }
        );
      }
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
  $main = new Main($argv);

  // Start the main loop.
  $main->loop();

  // Allow things to easily get the main class.
  function getMain() {
    return $GLOBALS['main'];
  }
?>
