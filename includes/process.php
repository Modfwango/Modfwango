<?php
  class Process {
    // Store configuration and state information
    private $configured = false;
    private $options    = array();
    private $process    = null;

    // Pipes, and process information
    private $err = -1;
    private $in  = -1;
    private $out = -1;
    private $pid = -1;

    // Executable path, arguments, and environment variables
    private $path = null;
    private $args = array();
    private $envs = array();

    public function __construct($path, $args = array(), $envs = array(),
        $options = array()) {
      // Check that the provided path is an executable file
      if (is_file($path) && is_executable($path)) {
        $this->path       = $path;
        $this->configured = true;
      }

      // Copy the provided arguments and environment variables
      $this->args = array_merge($this->args, $args);
      $this->envs = $envs;

      // Assign the given options
      $this->options = $options;

      Logger::debug("Created process \"".$this->path."\" with arguments \"".
        var_export($args, true)."\" and environment variables \"".
        var_export($this->envs, true)."\"");
    }

    public function addArgument($arg) {
      // Add the requested argument
      $this->args[] = $arg;
    }

    public function addArguments($args) {
      // Add the requested arguments
      $this->args = array_merge($this->args, $args);
    }

    public function addEnvironment($env) {
      // Add the requested argument
      $this->envs[] = $env;
    }

    public function addEnvironments($envs) {
      // Add the requested arguments
      $this->envs = array_merge($this->envs, $envs);
    }

    public function check() {
      // Determine if there is still data to be read from the process
      $hasData = false;
      if (is_resource($this->err))
        $hasData |= !feof($this->err);
      if (is_resource($this->out))
        $hasData |= !feof($this->out);
      // If the process has been started, and isn't running
      if (is_resource($this->process)) {
        $status = @proc_get_status($this->process);
        // Clean up if the process has stopped running
        if (is_array($status) && $status['running'] == false &&
            $hasData == false) {
          $this->stop();
          return false;
        }
      }
      else if ($this->pid > 0 && $hasData == false) {
        $this->stop();
        return false;
      }
      // If the process hasn't been started, and isn't running
      else
        return false;
      return true;
    }

    public function clearArguments() {
      // Clear the arguments array
      $this->args = array();
    }

    public function clearEnvironments() {
      // Clear the environments array
      $this->envs = array();
    }

    public function getData($err = false) {
      $fd = ($err ? $this->err : $this->out);
      // Check to make sure the process is a valid resource
      if (is_resource($fd)) {
        // Attempt to read data from the process
        if ($data = @stream_get_line($fd, 8192, "\n")) {
          // Sanitize data
          $data = trim($data);
          // Return the data
          Logger::devel("Data received from '".$this->path."':  '".$data."'");
          return $data;
        }
        else
          // Ensure the Process is still running
          $this->check();
      }
      return false;
    }

    public function getOption($key) {
      // Retrieve the requested option if it exists, otherwise return false
      return (isset($this->options[$key]) ? $this->options[$key] : false);
    }

    public function getPath() {
      return $this->path;
    }

    public function getPID() {
      return $this->pid;
    }

    public function getSTDERR() {
      return $this->err;
    }

    public function getSTDIN() {
      return $this->in;
    }

    public function getSTDOUT() {
      return $this->out;
    }

    public function send($data, $newline = true) {
      // Check to make sure the pipe is a valid resource
      if (is_resource($this->in)) {
        if ($data != null)
          Logger::devel("Sending data to client:  '".$data."'");
        // Send data to the client
        if ($newline == true)
          $data .= "\r\n";
        $status = @fputs($this->in, $data, strlen($data));

        // Disconnect if an error occurred
        if ($status === false)
          $this->stop();
      }
      return $this->check();
    }

    public function setOption($key, $value) {
      // Set an option for this connection
      $this->options[$key] = $value;
      return true;
    }

    public function start() {
      // Refuse to run if not configured properly
      if ($this->configured != true || $this->check() == true)
        return false;

      // Prepare arguments to append to the command path
      $args = implode(' ', array_map('escapeshellarg', $this->args));

      // Pipe descriptions (from the child's perspective)
      $fd = array(
        0 => array('pipe', 'r'), // stdin
        1 => array('pipe', 'w'), // stdout
        2 => array('pipe', 'w')  // stderr
      );

      // Open the process
      Logger::debug("Starting process \"".$this->path.' '.$args."\" with ".
        "environment variables \"".var_export($this->envs, true)."\" ...");
      $this->process = @proc_open($this->path.' '.$args, $fd, $pipes,
        getcwd(), $this->envs);

      // If the process failed to start, reset the resource variable
      if (!is_resource($this->process)) {
        Logger::debug("Failed to start process \"".$this->path."\" ...");
        $this->process = null;
        return false;
      }
      else {
        // Copy the pipes
        $this->err = $pipes[2];
        $this->in  = $pipes[0];
        $this->out = $pipes[1];

        // Set pipes to non-blocking
        stream_set_blocking($this->err, 0);
        stream_set_blocking($this->in,  0);
        stream_set_blocking($this->out, 0);

        // Log the pipes that we received
        Logger::debug("Got pipe file descriptors: ".var_export(
          array_map('is_resource', $pipes), true));
        Logger::debug("Copied file descriptors: ".var_export(
          array_map('is_resource', array($this->in, $this->out, $this->err)),
          true));

        // Fetch the PID of the process
        $status = proc_get_status($this->process);
        $this->pid = $status['pid'];

        // Get the associated event
        $name  = "processStartedEvent";
        $event = EventHandling::getEventByName($name);
        if ($event != false)
          foreach ($event[2] as $id => $registration)
            // Trigger the event for each registration
            EventHandling::triggerEvent($name, $id, $this);
      }
      return true;
    }

    public function stop() {
      Logger::debug("Stopping process \"".$this->path."\" ...");
      // Close the pipes for the process
      @fclose($this->err);
      @fclose($this->in);
      @fclose($this->out);
      $this->err = $this->in = $this->out = -1;

      // Terminate & close the process
      @proc_terminate($this->process);
      @proc_close($this->process);
      $this->process = null;

      // Get the associated event
      $name  = "processStoppedEvent";
      $event = EventHandling::getEventByName($name);
      if ($event != false)
        foreach ($event[2] as $id => $registration)
          // Trigger the event for each registration
          EventHandling::triggerEvent($name, $id, $this);
    }
  }
