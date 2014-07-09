Modfwango
=========

Modfwango is a modular socket framework written in PHP.  This framework has been
refined over the past few years for personal use, and I decided to make it open
to the public.  It is stable, clean, modular, and object oriented.  This
particular repo stemmed from the IRCBot-PHP framework, formerly known as
Modfwango.  I decided to make the separation, because a networking framework
with reloadable modules is of more use to people than just a simple IRC bot.

Install
=======

This framework is tested under Ubuntu Linux and Mac OS X.  Windows compatibility
is unknown, and probably unstable.  To use this framework, make sure that you
have the latest version of PHP 5 CLI installed on your machine.

In order to setup a Modfwango-based project, decide on a project name, then run
the following commands:
```
mkdir ProjectName && cd ProjectName
git init
git submodule add https://github.com/ClayFreeman/Modfwango.git .modfwango
cp .modfwango/launcher.php .
php launcher.php
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

#### conf/connections/<name>.conf
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
decides it want's to be loaded, or otherwise return false
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

#### Using Available APIs

Modfwango comes packed with some available APIs to allow creating modules to be
extremely easy.  A list of these are:
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

###### ConnectionManagement
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

###### Connection
TODO:  Complete documentation.

Support
=======

For support with this bot's framework, open a ticket, or email me using my email
address on GitHub.
