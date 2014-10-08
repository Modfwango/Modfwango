Change Log
==========

###### 1.24 - October 7, 2014
Rearrange the documentation into the docs/ folder in the project root.

Add some inline documentation to the launcher.

###### 1.23 - September 28, 2014
Add the `getPath()` method to `StorageHandling` to enable fetching a simplified,
correct path to a file or folder.

###### 1.22 - September 19, 2014
Fix a few bugs with the `IPCHandling` class, the `Logger` class, and the
`StorageHandling` class.

###### 1.21 - September 6, 2014
Add inter-process communication handling and modify the rest of the framework to
integrate it.

Update `Logger` to separate multiple lines into multiple `Logger` calls and
right align the log type prefix.

###### 1.20 - August 17, 2014
Deprecate `@@CLASSNAME@@` for better alternative `__CLASSNAME__` that satisfies
code validation and text editor requirements.

Update all core modules to use `__CLASSNAME__`.

###### 1.19 - August 16, 2014
Prevent a module from being unloaded if another module which is unloadable
depends upon it.

###### 1.18 - August 16, 2014
Allow modules to explicitly declare if they're reloadable or unloadable.

###### 1.17 - August 12, 2014
Rebase efforts to the Modfwango organization on GitHub.

###### 1.16 - August 12, 2014
Fix minor PHP warning about an undefined variable.

###### 1.15 - August 5, 2014
Recursively check dependencies.

###### 1.14 - August 5, 2014
Make the `created()` method in `Connection` available to child classes.

###### 1.13 - August 5, 2014
Make parent class properties accessible from child classes.

###### 1.12 - August 5, 2014
Added support for pseudo-connection classes to be added to the connection
manager for fake clients.

###### 1.11 - August 3, 2014
General improvements to the Modfwango project.

###### 1.10 - July 10, 2014
Add SSL server socket capabilities.  Denote SSL bindings with a `+` before the
port number in `listen.conf`.

Added update checker capability to inform users when there is an update to
Modfwango.

###### 1.04 - July 9, 2014
Remove update script since it would require local git commits to update core.

###### 1.03 - July 9, 2014
Fix problem with deferred modules causing Modfwango to exit.

###### 1.02 - July 9, 2014
Make un-loadable runtime modules a fatal error.

###### 1.01 - July 9, 2014
Simplified some things, added a launcher and updater.

###### 1.00 - July 9, 2014
Initial commit.
