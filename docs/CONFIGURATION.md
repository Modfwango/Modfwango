Table of Contents
=================

* [conf/listen.conf](#conflistenconf)
* [conf/modules.conf](#confmodulesconf)
* [conf/connections/name.conf](#confconnectionsnameconf)

Configuration
=============
To configure Modfwango, follow these simple guidelines:

#### conf/listen.conf
If you require a socket server to listen for connections, you need to configure
this file.  The syntax for this file is the listen address, followed by a comma,
followed by the port you'd like to listen on.  Ports can be prefixed with a `+`
in order to make them listen with SSL enabled.  If you need to use your own
certificate, put it in place of the one that is automatically generated when you
first listen on SSL (the path to it will be logged when the certificate is
generated).  Multiple entries should each be on a line by themselves.  An
example configuration is shown below.
```
0.0.0.0,1337
0.0.0.0,+1338
```

The path for SSL certificates will be formatted as so when they're generated:
```
conf/ssl/[port]/[address].pem
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
