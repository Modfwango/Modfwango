<?php
  class Main {
    public function __construct($argv) {
      // Verify that the bot can run in the provided environment
      $this->verifyEnvironment();

      // Verify that this project isn't already running
      $pidfile = __PROJECTROOT__."/data/".basename(__PROJECTROOT__).".pid";
      if (is_readable($pidfile)) {
        $pid = file_get_contents($pidfile);
        if (posix_getpgid($pid)) {
          echo "Already running with PID ".intval($pid)."\n";
          exit(1);
        }
        // Remove the PID file if we're not /actually/ already running
        unlink($pidfile);
      }
      elseif (file_exists($pidfile)) {
        echo "Can't read PID file \"".$pidfile."\"\n";
        exit(1);
      }

      // Write PID
      $this->writePID();

      if (isset($argv[1])) {
        // Ensure that any non-default user input is converted to an integer
        $loglevel = (int)$argv[1];
      }
      elseif (is_readable(__PROJECTROOT__."/conf/loglevel.conf")) {
        // Read the log level from conf/loglevel.conf
        $loglevel = (int)file_get_contents(__PROJECTROOT__.
          "/conf/loglevel.conf");
      }
      else {
        $loglevel = 0;
      }

      // Set the process name using a config file
      if (version_compare(phpversion(), '5.5', '>=')) {
        @cli_set_process_title("modfwango");
        if (is_readable(__PROJECTROOT__."/conf/name.conf")) {
          // Read the name from conf/name.conf
          $title = explode("\n", trim(file_get_contents(__PROJECTROOT__.
            "/conf/name.conf")));
          $title = trim($title[0]);
          if (strlen($title) > 0) {
            // Only set the title if it is not null
            @cli_set_process_title($title);
          }
        }
      }

      // Activate full error reporting
      $this->setErrorReporting();

      // Setup required constants for operation and load required classes
      $this->prepareEnvironment($loglevel);

      // Brag about versions and junk
      $this->brag();

      // Load requested modules
      $this->loadModules();

      // Discover sockets located in conf/listen.conf
      $this->discoverSockets();

      // Background the process (if we can/should)
      $this->background();

      // Discover connections located in conf/connections/
      $this->discoverConnections();

      // Initiate all loaded connections
      $this->activateConnections();
    }

    private function activateConnections() {
      // Iterate through the list of defined connections
      foreach (ConnectionManagement::getConnections() as $connection) {
        // Connect
        $connection->connect();
      }
    }

    private function background() {
      // Only background if we're in silent mode and have PCNTL
      if (__LOGLEVEL__ == 0 && (!class_exists('Shell') || !Shell::started()) &&
          function_exists("pcntl_fork") && function_exists("pcntl_fork")) {
        if ($pid = pcntl_fork()) {
          exit(0);
        }

        // Discard the output buffer and close
        @ob_end_clean();

        // Hide all further output
        ob_start();

        register_shutdown_function(
          function() {
            posix_kill(posix_getpid(), SIGHUP);
          }
        );

        if (posix_setsid() < 0 || $pid = pcntl_fork()) {
          exit(0);
        }

        // Update PID
        $this->writePID();

        // Get the backgroundEvent event
        $event = EventHandling::getEventByName("backgroundEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the backgroundEvent event for each registered module
            EventHandling::triggerEvent("backgroundEvent", $id);
          }
        }
      }
    }

    private function brag() {
      Logger::info("Welcome to Modfwango!");
      Logger::info("You're running Modfwango v".
        __MODFWANGOVERSION__.".");
      // Check for updates to Modfwango
      if (!file_exists(__PROJECTROOT__."/conf/noupdatecheck")) {
        $contents = @explode("\n", @file_get_contents("https://raw.githubuserc".
          "ontent.com/Modfwango/Modfwango/master/docs/CHANGELOG.md", 0,
          stream_context_create(array('http' => array('timeout' => 1)))));
        if (is_array($contents)) {
          foreach ($contents as $line) {
            if (preg_match("/^[#]{6} (.*)$/i", trim($line), $matches)) {
              $v = explode(" ", $matches[1]);
              $v = trim($v[0]);
              if (version_compare(__MODFWANGOVERSION__, $v, "<")) {
                Logger::info("An update is available at http://modfwango.com/");
              }
              break;
            }
          }
        }
      }
    }

    private function discoverConnections() {
      // Get a list of connection configurations
      $connections = glob(__PROJECTROOT__."/conf/connections/*.conf");

      // Iterate through the list and load each item individually
      foreach ($connections as $file) {
        ConnectionManagement::loadConnectionFile($file);
      }
    }

    private function discoverSockets() {
      // Check if listen.conf is readable
      $listen = __PROJECTROOT__."/conf/listen.conf";
      if (is_readable($listen)) {
        // Load the listen configuration
        $listen = trim(file_get_contents($listen));
        $listen = explode("\n", $listen);
        // Iterate through each line
        foreach ($listen as $sock) {
          // Make sure there are no stray line endings
          $sock = trim($sock);
          // Make sure the line has a non-null value and a comma
          if (strlen($sock) > 0 && strstr($sock, ",")) {
            // Separate bind address from bind port
            $sock = explode(",", $sock);
            // Make sure we have the correct amount of parameters
            if (count($sock) == 2) {
              // Attempt to create a socket
              $sock = new Socket(trim($sock[0]), trim($sock[1]));
              if ($sock != false) {
                // Add it to the socket management class
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

      if (function_exists("pcntl_fork")) {
        // Attempt to create the inter-process communication socket
        $sock = new Socket("127.0.0.1", "0", true);
        if ($sock != false) {
          // Add it to the socket management class
          SocketManagement::newSocket($sock);
        }
        else {
          // Couldn't bind!
          Logger::debug("Could not bind to address.");
        }
      }
    }

    private function loadModules() {
      // Define an array of paths to config files and module roots
      $paths = array(
        __MODFWANGOROOT__."/conf/modules.conf",
        __PROJECTROOT__."/conf/modules.conf"
      );

      // Load modules in requested order in each path
      foreach ($paths as $path) {
        // Make sure path is readable
        if (is_readable($path)) {
          // Iterate over each line
          foreach (explode("\n", trim(file_get_contents($path))) as $line) {
            $line = trim($line);
            if (strlen(trim($line)) > 0) {
              // Attempt to load the requested module
              if (ModuleManagement::loadModule($line) === false) {
                Logger::info("Module \"".$line."\" failed to load.");
                exit(1);
              }
            }
          }
        }
      }
    }

    public function loop() {
      // Clear the output buffer of the Shell
      if (class_exists('Shell')) Shell::clearOutput();
      // Infinitely loop
      while (true) {
        // Fetch input from STDIN if Shell is activated
        if (class_exists('Shell') && Shell::started())
          Shell::processInput();

        // Iterate through each socket
        foreach (SocketManagement::getSockets() as $socket) {
          // Attempt to accept new connections
          $socket->accept();
        }

        // Prune dead connections
        ConnectionManagement::pruneConnections();
        // Prune dead processes
        ProcessManagement::pruneProcesses();

        // Iterate through each connection
        foreach (ConnectionManagement::getConnections() as $connection) {
          // Fetch any received data
          $data = $connection->getData();
          if ($data !== false) {
            foreach (explode("\n", $data) as $line) {
              if (function_exists("pcntl_fork")
                  && $connection->getIPC() == true) {
                // Pass the connection and associated data to the IPC handler
                IPCHandling::receiveData($connection, trim($line));
              }
              else {
                // Pass the connection and associated data to the event handler
                EventHandling::receiveData($connection, trim($line));
              }
            }
          }
        }

        // Iterate through each process
        foreach (ProcessManagement::getProcesses() as $process) {
          // Fetch any received data
          $outdata = $process->getData();
          $errdata = $process->getData(true);
          if ($outdata !== false || $errdata !== false) {
            $data = $outdata.$errdata;
            foreach (explode("\n", $data) as $line) {
              $name  = "processDataEvent";
              $event = EventHandling::getEventByName($name);
              if ($event != false)
                foreach ($event[2] as $id => $registration) {
                  // Trigger the processDataEvent for each received line of data
                  // from this process
                  EventHandling::triggerEvent($name, $id, array($process,
                    $data, explode(" ", $data)));
                }
            }
          }
        }

        // Get the connectionLoopEndEvent event
        $event = EventHandling::getEventByName("connectionLoopEndEvent");
        if ($event != false) {
          foreach ($event[2] as $id => $registration) {
            // Trigger the connectionLoopEndEvent event for each registered
            // module
            EventHandling::triggerEvent("connectionLoopEndEvent", $id);
          }
        }
        // Sleep for a small amount of time to prevent high CPU usage
        usleep(__DELAY__);
      }
    }

    private function prepareEnvironment($loglevel) {
      // Define the root of the Modfwango library folder
      define("__MODFWANGOROOT__", dirname(__FILE__));

      // Locate the latest version in docs/CHANGELOG.md
      $contents = __MODFWANGOROOT__."/docs/CHANGELOG.md";
      $version = "1.00";
      if (is_readable($contents)) {
        $contents = explode("\n", file_get_contents($contents));
        foreach ($contents as $line) {
          if (preg_match("/^[#]{6} (.*)$/i", trim($line), $matches)) {
            $version = explode(" ", $matches[1]);
            $version = trim($version[0]);
            break;
          }
        }
      }

      // Define the current version of Modfwango
      define("__MODFWANGOVERSION__", $version);

      // Change current working directory to project root
      chdir(__PROJECTROOT__);

      // Set the default timezone
      if (defined("__TIMEZONE__")) {
        date_default_timezone_set(__TIMEZONE__);
      }

      // Define start timestamp
      define("__STARTTIME__", time());

      // Define the time to sleep at the end of every infinite loop
      define("__DELAY__", 5000);

      // Define the debug constant to allow the logger determine the correct
      // output type
      define("__LOGLEVEL__", $loglevel);

      // Load the Shell class if ncurses is available
      if (function_exists('ncurses_init'))
        require_once(__MODFWANGOROOT__."/includes/shell.php");

      // Load the logger
      require_once(__MODFWANGOROOT__."/includes/logger.php");

      // Load the connection and process related classes
      require_once(__MODFWANGOROOT__."/includes/connection.php");
      require_once(__MODFWANGOROOT__."/includes/connectionManagement.php");
      require_once(__MODFWANGOROOT__."/includes/socket.php");
      require_once(__MODFWANGOROOT__."/includes/socketManagement.php");
      require_once(__MODFWANGOROOT__."/includes/process.php");
      require_once(__MODFWANGOROOT__."/includes/processManagement.php");

      if (function_exists("pcntl_fork")) {
        // Load the inter-process communication handler
        require_once(__MODFWANGOROOT__."/includes/IPCHandling.php");
      }
      else {
        Logger::debug("PCNTL support isn't available.");
      }

      // Load the event handler
      require_once(__MODFWANGOROOT__."/includes/eventHandling.php");

      // Make sure the launcher is up-to-date
      $launcher = __MODFWANGOROOT__."/launcher.php";
      $main = __PROJECTROOT__."/main.php";
      if (!file_exists(__PROJECTROOT__."/main.php") ||
          (is_readable($launcher) && is_readable($main) &&
           hash("md5", file_get_contents($launcher)) !=
           hash("md5", file_get_contents($main)))) {
        file_put_contents($main, file_get_contents($launcher));
        Logger::info("The launcher has been updated.");
      }

      // Load the module management class
      require_once(__MODFWANGOROOT__."/includes/moduleManagement.php");

      // Load the storage handling class
      require_once(__MODFWANGOROOT__."/includes/storageHandling.php");

      // Register signal handlers
      declare(ticks = 1);
      register_shutdown_function(array($this, "shutdown"), false);
      if (function_exists("pcntl_signal"))
        pcntl_signal(SIGINT, function() { exit(0); });
    }

    public function shutdown($exit = true) {
      echo "\r";
      Logger::info("Begin shutdown procedure...");
      foreach (ConnectionManagement::getConnections() as $c) {
        $c->disconnect();
      }
      foreach (SocketManagement::getSockets() as $s) {
        $s->close();
      }
      Logger::info("Shutting down...");
      if ($exit == true) {
        exit(0);
      }
    }

    private function setErrorReporting() {
      error_reporting(E_ALL);
      ini_set("display_errors", 1);
      set_error_handler(function($errno, $errstr) {
        if (error_reporting() & $errno) {
          // Print a backtrace if this error is supposed to be shown
          Logger::info(var_export(debug_backtrace(), true));
        }
        return false;
      });
    }

    private function verifyEnvironment() {
      // Verify that the current directory structure is named safely
      if (!preg_match("/^[a-zA-Z0-9\\/._-]+$/", dirname(__FILE__))) {
        echo "The full path to this file must match this regular expression:\n".
          "^[a-zA-Z0-9/._-]+$\n";
        exit(1);
      }

      // Verify that the launcher script has setup the project root constant
      if (!defined("__PROJECTROOT__")) {
        echo "__PROJECTROOT__ hasn't been defined by a launcher script.\n";
        exit(1);
      }
    }

    private function writePID() {
      // Make note of our pid
      file_put_contents(__PROJECTROOT__."/data/".basename(__PROJECTROOT__).
        ".pid", posix_getpid());
    }
  }

  // Instantiate the bot to get things moving
  $main = new Main($argv);

  // Start the main loop
  $main->loop();

  // Allow things to easily get the main class
  function getMain() {
    return $GLOBALS['main'];
  }
?>
