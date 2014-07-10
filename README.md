![Modfwango](http://dpr.clayfreeman.com/zarH+ "Modfwango")

Modfwango is a modular socket framework written in PHP.  This framework has been
refined over the past few years for personal use, and I decided to make it open
to the public.  It is stable, clean, modular, and object oriented.  This
particular repo stemmed from the
[IRCBot-PHP](https://github.com/ClayFreeman/IRCBot-PHP) framework, formerly
known as Modfwango.  I decided to make the separation, because a networking
framework with reloadable modules is of more use to people than just a simple
IRC bot.

Table of Contents
=================

* [Install](#install)
* [Update](#update)
* [Configuration](#configuration)
  * [conf/listen.conf](#conflistenconf)
  * [conf/modules.conf](#confmodulesconf)
  * [conf/connections/name.conf](#confconnectionsnameconf)
* [Development](#development)
  * [Creating Your First Module](#creating-your-first-module)
  * [Dependencies](#dependencies)
  * [Using Available APIs](#using-available-apis)
    * [ConnectionManagement](#connectionmanagement)
    * [Connection](#connection)
      * [Methods](#methods)
    * [EventHandling](#eventhandling)
      * [registerForEvent](#registerforevent)
      * [registerAsEventPreprocessor](#registeraseventpreprocessor)
      * [Methods](#methods-1)
    * [ModuleManagement](#modulemanagement)
    * [SocketManagement](#socketmanagement)
    * [Socket](#socket)
      * [Methods](#methods-2)
    * [StorageHandling](#storagehandling)
  * [Examples](#examples)
* [Support](#support)

Install
=======

This framework is tested under Ubuntu Linux and Mac OS X.  Windows compatibility
is unknown, and probably unstable.  To use this framework, make sure that you
have at least version 5.1.1 of PHP (CLI) installed on your machine.

In order to setup a Modfwango-based project, decide on a project name, then run
the following commands:
```
mkdir ProjectName && cd ProjectName
git init
git submodule add https://github.com/ClayFreeman/Modfwango.git .modfwango
cp .modfwango/launcher.php main.php
php main.php
```

Those commands will initialize a git repository, add Modfwango as a submodule,
provide you with a launcher for your project, and create a base directory/file
structure.

Update
======

In order to update Modfwango, a file called `update.sh` should have been placed
into your project's root directory.  Simply run `sh update.sh` to update
Modfwango.

Configuration
=============
To configure Modfwango, follow these simple guidelines:

#### conf/listen.conf
If you require a socket server to listen for connections, you need to configure
this file.  The syntax for this file is the listen address, followed by a comma,
followed by the port you'd like to listen on.  Multiple entries should each be
on a line by themselves.  An example configuration is shown below.
```
0.0.0.0,1337
127.0.0.1,1338
```

#### conf/modules.conf
This file is likely to be required by everyone; if you intend on implementing
custom functionality in your project, you need to do it with a module.  This
file allows for modules to be auto-loaded at runtime.  Each module should be on
its own line, and should be named relative to the `modules` directory inside of
your project's root folder.  Modules should not include the `.php` file
extension.  Modules can be nested in an infinite amount of folders.  An example
configuration file is shown below.  Order doesn't matter; modules will be loaded
after all of their dependencies have been loaded.
```
events/ConnectionConnectedEvent
events/ConnectionCreatedEvent
events/ConnectionDisconnectedEvent
events/ConnectionLoopEndEvent
events/RawEvent

libraries/Timer
```

#### conf/connections/name.conf
If you need to connect to other servers, you need to create a file for each
connection.  Each file is parsed with the built-in PHP INI file parser.  An
example file with all parameters is shown below.  You can optionally include the
`[options]` block, which will be available for use during runtime.
```
address = "example.org"
port = 1337
ssl = false

[options]
param1 = "hello"
param2 = "world"
```

Development
===========

#### Creating Your First Module

Each module has a specific set of requirements in order to be compliant with the
`ModuleManagement` class:
* Must be located somewhere inside the `modules` directory in your project root
* Must be inside of a class named `@@CLASSNAME@@`
* Must have a non-static property `$name` defined as the base filename without
its extension
* Must have a non-static method `isInstantiated` that returns true if the module
decides it wants to be loaded, or otherwise return false
* Must have opening and closing `<?php` `?>` tags surrounding code

Here is an example module that does absolutely nothing except allow itself to be
loaded:
```php
<?php
  class @@CLASSNAME@@ {
    public $name = "ModuleFileName";

    public function isInstantiated() {
      return true;
    }
  }
?>
```

#### Dependencies
Modules can establish certain dependencies that are required in order for them
to load.  This can be done by defining a class property named `$depend` with a
value of an array with strings of module names.  An example is shown below:
```php
<?php
  class @@CLASSNAME@@ {
    public $depend = array("ConnectionConnectedEvent",
      "ConnectionDisconnectedEvent");
    public $name = "Example";

    public function isInstantiated() {
      return true;
    }
  }
?>
```

#### Using Available APIs

Modfwango comes packed with some APIs to allow creating modules to be extremely
easy.  Each class with "Management" or "Handling" in the name is a completely
static class.  These APIS consist of:
* `ConnectionManagement`, `Connection` - Allows you to define, undefine,
connect, and disconnect connections.
* `EventHandling` - Allows you to create, preprocess, and register for events
* `ModuleManagement` - Allows you to control which modules are loaded at any
given time
* `SocketManagement`, `Socket` - Allows you to define and undefine server
sockets that will accept client connections.
* `StorageHandling` - Allows you to create, delete, write, and read files
through a simple interface.

Let's go over each API, shall we?

##### ConnectionManagement
`ConnectionManagement` allows you to control incoming or outgoing connections.
`ConnectionManagement` has the following available methods:
* `bool newConnection(Connection $connection)` - Adds a connection to the
connection manager
* `mixed getConnectionByHost(String $host)` - Returns a connection that has the
provided host, or returns false
* `mixed getConnectionByIndex(int $index)` - Returns a connection that has the
provided index, or returns false
* `bool delConnectionByHost(String $host)` - Removes a connection that has the
provided host, or returns false
* `bool delConnectionByIndex(int $index)` - Removes a connection that has the
provided index, or returns false
* `mixed getConnectionIndexByHost(String $host)` - Returns the index of a
connection that has the provided host, or returns false
* `array getConnections()` - Returns an array of all connections known by
the connection manager
* `bool loadConnectionFile(String $file, bool $autoconnect)` - Returns true upon
successful loading of a connection configuration file, or false on failure.
Auto-connects to the connection if specified

Since `ConnectionManagement` is responsible for managing `Connection` classes,
we'll cover those next.

##### Connection
The `Connection` constructor method is structured as so:
```
bool __construct(String $type, array $data)
```
`Connection` accepts two parameters:
* The type of connection (`"0"` for outgoing, `"1"` for incoming)
* The associated data array

Currently the data array should contain the following parameters for outgoing
connections:
* A string of the target hostname
* A string/int of the target port
* A bool of whether or not SSL should be used
* [Optional] An array of options

The data array should be structured as so for outgoing connections (the last
key is optional):
```
array(
  0 => "hostname.example.org",
  1 => "1337",
  2 => true,
  3 => array(
    "optional" => "setting",
    "other" => "setting1"
  )
)
```

Currently the data array should contain the following parameters for incoming
connections:
* The socket
* A string/int of the target port
* A bool of whether or not SSL should be used
* [Optional] An array of options

The data array should be structured as so for incoming connections (the last
key is optional):
```
array(
  0 => $socket,
  1 => "1337",
  2 => false,
  3 => array(
    "optional" => "setting",
    "other" => "setting1"
  )
)
```

And finally, a new connection should be created as so:
```
$connection = new Connection("0", array("example.org", 1337, false, array()));
```

###### Methods

After a connection has been created, you can use the following available
methods:
* `bool configured()` - Returns whether or not the `Connection` was properly
configured
* `bool connect()` - Attempts to connect to the specified host/port, returns
true if successful, returns false upon error or if `$type` is "1"
* `bool disconnect()` - Disconnects from the socket, returns true if successful,
returns false if socket property is not a valid resource
* `mixed getData()` - Retrieves data from the socket, returns a string on
success, or false on failure
* `String getConnectionString()` - Returns a string describing the connection
* `String getHost()` - Returns the hostname of the remote connection endpoint
* `String getIP()` - Returns the IP of the remote connection endpoint
* `String getLocalHost()` - Returns the hostname of the local connection
endpoint
* `String getLocalIP()` - Returns the IP of the local connection endpoint
* `mixed getOption(String $key)` - Returns the value of option by `$key`,
returns false if it doesn't exist
* `mixed getPort()` - Returns string/int of connection port
* `bool getSSL()` - Returns true if using SSL, false if using plaintext
* `String getType()` - Returns "0" if outgoing connection, returns "1" if
incoming connection
* `bool isAlive()` - Returns true if socket is a resource, false if it isn't
* `bool send(String $data, bool $newline = true)` - Returns true on successful
data transmission, false on failure
* `bool setOption(String $key, String $value)` - Sets option by `$key` to
`$value`, always returns true

##### EventHandling
`EventHandling` is essentially the core idea of the Modfwango project.  The
`EventHandling` class encompasses the majority of how functionality is usually
implemented through modules.  Basically, when Modfwango receives data from any
given `Connection` class, it triggers the `EventHandling::receiveData()` method
which gives modules that provide events the opportunity to trigger themselves
for each of their registrations.  This allows you to make events that trigger
automatically, for example, when a certain string of data is sent, or if certain
conditions are met.  An event module can be as simple as shown below:
```php
<?php
  class @@CLASSNAME@@ {
    public $name = "ConnectionConnectedEvent";

    public function isInstantiated() {
      EventHandling::createEvent("connectionConnectedEvent", $this);
      return true;
    }
  }
?>
```

The above event registers itself as a module by the name of
`ConnectionConnectedEvent` and creates an event called `connectionCreatedEvent`.
This event requires manual triggering since it doesn't register a data
preprocessor callback (the third parameter to `EventHandling::createEvent()`).
An example of an event that includes a data preprocessor is shown below:
```php
<?php
  class @@CLASSNAME@@ {
    public $name = "RawEvent";

    public function preprocessEvent($name, $registrations, $connection, $data) {
      $ex = explode(" ", $data);

      // Iterate through each registration.
      foreach ($registrations as $id => $registration) {
        // Trigger the event for a certain registration.
        EventHandling::triggerEvent($name, $id, array($connection, $data, $ex));
      }
    }

    public function isInstantiated() {
      // Create an event for raw data.
      EventHandling::createEvent("rawEvent", $this, "preprocessEvent");
      return true;
    }
  }
?>
```

The above event registers itself as a module by the name of `RawEvent` and
creates an event called `rawEvent`.  This event is triggered any time data is
received. This can be useful in very few cases, but it's useful nonetheless.

###### registerForEvent

A module can register for an event by calling
`bool EventHandling::registerForEvent(String $name, Object $module,
String $callback, mixed $data = null)`.  This method will return true if the
registration was successful, or false if it didn't succeed.  An example module
that registers for an event is shown below:
```php
<?php
  class @@CLASSNAME@@ {
    public $depend = array("RawEvent");
    public $name = "DataLogging";

    public function receiveRaw($name, $data) {
      $connection = $data[0];
      $data = $data[1];

      Logger::info("Data received from ".$connection->getConnectionString().
        ":  \"".$data."\"");
    }

    public function isInstantiated() {
      EventHandling::registerForEvent("rawEvent", $this, "receiveRaw");
      return true;
    }
  }
?>
```

This module registers for the `rawEvent` previously shown.  Any time that data
is received, this module's `receiveRaw` method is called.  Each event
registration must have a callback that conforms to the protocol
`callback(String $name, mixed $data)`.  This ensures that your event
registration is called appropriately.  `$name` provides you with the event name
so that you can register more than one event to the same callback, and `$data`
provides you with something that each specific event thinks is important for you
to have.  This item will vary for each event.

###### registerAsEventPreprocessor

A module can register as an event preprocessor by calling
`bool EventHandling::registerAsEventPreprocessor(String $name, Object $module,
String $callback)`.  This method will return true if the registration was
successful, or false if it didn't succeed.  Registering as an event preprocessor
gives your module the opportunity to alter the event, or outright refuse the
event to trigger.  An example module that registers as an event preprocessor is
shown below:
```php
<?php
  class @@CLASSNAME@@ {
    public $depend = array();
    public $name = "IgnoreConnectionConnectedEvent";

    public function receiveConnectionConnected($name, $id, $data) {
      return array(false);
    }

    public function isInstantiated() {
      EventHandling::registerAsEventPreprocessor("connectionConnectedEvent",
        $this, "receiveConnectionConnected");
      return true;
    }
  }
?>
```

This module registers as a preprocessor for the `connectionConnectedEvent`.  Any
time that the `connectionConnectedEvent` attempts to trigger, this module's
`receiveConnectionConnected` method is called.  Each event preprocessor
registration must have a callback that conforms to the protocol
`callback(String $name, int $id, mixed $data)`.  This ensures that your event
preprocessor registration is called appropriately.  `$name` provides you with
the event name so that you can register more than one preproocessor to the same
callback, `$id` provides you with the specific registration ID, and `$data`
provides you with something that each specific event thinks is important for you
to have.  This item will vary for each event.

When preprocessing an event, `EventHandling` looks for two things:
* Your return value must be an array or it is ignored
* Your return value must be equal to `array(false)` or
`array(null, mixed $data)` before any action is taken (`array(true)` is just
ignored since it would be considered a successful preprocessor passthrough)

There are two types of return values that alter the event's state:
* `array(null, mixed $data)` - Tells `EventHandling` that you desire to replace
the previous `$data` with a new value
* `array(false)` - Tells `EventHandling` that the event should be cancelled

###### Methods

A full list of methods for the `EventHandling` class is listed below:
* `bool function createEvent(String $name, Object $module,
String $callback = null)` - Allows you to create an event named `$name` with an
origin from your `$module`, with an optional callback of `$callback` to
preprocess data.  Returns true on successful creation, false upon failure
* `bool function destroyEvent(String $name)` - Removes an event named `$name`,
returns true if successful, false if the event didn't exist
* `mixed function getEventByName(String $name)` - Returns an event with the
specified `$name`, or false if the event didn't exist
* `array function getEvents()` - Returns an array of all events
* `bool function receiveData(Connection $connection, String $data)` - Allows you
to simulate incoming `$data` from a specified `$connection`
* `bool function registerForEvent(String $name, Object $module,
String $callback, mixed $data = null)` - Allows you to register a `$callback` in
your `$module` for the event with specified `$name`.  You can optionally include
data to the event's data preprocessor if it exists through the `$data` variable.
Returns true if registration successful, false if an error occured
* `bool function registerAsEventPreprocessor(String $name,
Object $module, String $callback)` - Allows you to register a `$callback` in
your `$module` for the event with specified `$name`
* `bool function triggerEvent(String $name, int $id,
mixed $data = null)` - Triggers a specific registration of `$id` for event named
`$name` with optional `$data`
* `bool function unregisterEvent(Object $module)` - Unregisters all events for
given `$module`
* `bool function unregisterForEvent(String $name, Object $module)` - Unregisters
`$module` for event named `$name`
* `bool function unregisterPreprocessorForEvent(String $name,
Object $module)` - Unregisters `$module` for event named `$name` as preprocessor
* `bool function unregisterModule(Object $module)` - Unregisters `$module` for
all event registrations and preprocessors

##### ModuleManagement
`ModuleManagement` enables you to control the current set of loaded modules.  A
list of methods for this class is shown below:
* `bool isLoaded(String $name)` - Returns true if a module with the given
`$name` is loaded, otherwise returns false
* `array getLoadedModules()` - Returns an array of all loaded module objects
* `array getLoadedModuleNames()` - Returns an array of names of all loaded
modules
* `mixed getModuleByName(String $name)` - Returns a module object with the given
`$name` if loaded, otherwise returns false
* `mixed loadModule(String $name, bool $suppressNotice = false)` - Returns true
if a module with the given `$name` can be loaded, null if the module cannot be
loaded because of missing dependencies, or false upon failure.  An optional
`$suppressNotice` flag can be set to true to hide the logging for the method
* `bool reloadModule(String $name)` - Simply calls `unloadModule` and
`loadModule` and returns true if both are successful, otherwise returns false
* `bool unloadModule(String $name, bool $suppressNotice = false)` - Unloads a
module with the given `$name` and returns true upon success, or false upon
failure.  Also includes the optional `$suppressNotice` variable to hide logging

##### SocketManagement
`SocketManagement` allows you to control server sockets that accept clients.
`SocketManagement` has the following available methods:
* `bool newSocket(Socket $socket)` - Adds a socket to the socket manager
* `mixed getSocketByHost(String $host)` - Returns a socket that has the provided
host, or returns false
* `mixed getSocketByIndex(int $index)` - Returns a socket that has the provided
index, or returns false
* `bool delSocketByHost(String $host)` - Removes a socket that has the provided
host, or returns false
* `bool delSocketByIndex(int $index)` - Removes a socket that has the provided
index, or returns false
* `mixed getSocketIndexByHost(String $host)` - Returns the index of a socket
that has the provided host, or returns false
* `array getSockets()` - Returns an array of all sockets known by the socket
manager

##### Socket
The `Socket` constructor method is structured as so:
```
bool __construct(String $host, mixed $port)
```

`Socket` accepts two parameters:
* A hostname or IP address bind string
* A string/int of the port to listen on

A listening socket should be created as so:
```
$socket = new Socket("0.0.0.0", 1337);
```

###### Methods
After a socket has been created, you can use the following available methods:
* `bool configured()` - Returns whether or not the `Socket` was properly
configured
* `bool close()` - Closes the socket, always returns true
* `String getHost()` - Returns the bind hostname
* `mixed getPort()` - Returns the bind port
* `String getSocketString()` - Returns a string describing the socket
* `bool accept()` - Attempts to accept a new client and add it to the
`ConnectionManager`.  Returns true if a client was accepted, otherwise false

##### StorageHandling
`StorageHandling` is a class that promotes hassle free management of storage.  A
list of available methods is shown below:
* `bool createDirectory(Object $module, String $name)` - Creates a directory
named `$name` in the given `$module`'s sandbox, returns true on success, or
false on error
* `mixed loadFile(Object $module, String $name)` - Returns the contents of a
file with given `$name` in the given `$module`'s sandbox, or false upon error
* `bool saveFile(Object $module, String $name, String $contents)` - Saves a
file named `$name` with given `$contents` in the specified `$module`'s sandbox

#### Examples
A few examples are listed below to demonstrate the above documentation for
Modfwango.
* [IRCBot-PHP](http://bit.ly/ircbot-php)
  * [modules/admin/Eval.php](http://bit.ly/ircbot-eval)
    * Great example of general module setup and logic
  * [modules/events/ChannelMessageEvent.php](http://bit.ly/ircbot-chanmsgevent)
    * Great use of event data preprocessors
* [IRCServer-PHP](http://bit.ly/ircserver-php)
  * [modules/modes/NoExternalMessages.php](http://bit.ly/ircserver-noextmsgs)
    * Great use of event trigger preprocessors

Support
=======

For support with this framework, join IRC at `irc.freenode.org` `#modfwango`,
open a ticket, or email me using my email address on GitHub.
