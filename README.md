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
mkdir <ProjectName> && cd <ProjectName>
git init
git submodule add https://github.com/ClayFreeman/Modfwango.git .modfwango
cp .modfwango/launcher.php .
php launcher.php
```

Those commands will initialize a git repository, add Modfwango as a submodule,
provide you with a launcher for your project, and create a base directory/file
structure.

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

Refer to the [wiki](https://github.com/ClayFreeman/Modfwango-Client/wiki) for
more information.

Support
=======

For support with this bot's framework, open a ticket, or email me using my email
address on GitHub.
