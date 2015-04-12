Change Log
==========

###### 1.36 - April 11, 2015
Added the `BackgroundEvent` to allow modules to be notified when Modfwango forks
into the background.

Allow setting a process title for PHP versions >= 5.5.

Modfwango now prints backtraces for non-fatal errors so that issues are easier
to track down.

###### 1.35 - December 23, 2014
General stability and performance improvements.

###### 1.34 - December 23, 2014
Connect to IP to prevent unnecessary DNS lookup.

###### 1.33 - December 18, 2014
Allow project to skip checking for updates to Modfwango by creating file
`conf/noupdatecheck` in the project root.

###### 1.32 - December 7, 2014
Strip non-alphanumeric characters from class names for modules.
Only allow `Connection` to connect if socket is `null`.

###### 1.31 - December 6, 2014
Allow optional `exit(0)` when using `Main->shutdown()`.

###### 1.30 - October 19, 2014
Switch all `die()` functions to `exit(int)` for proper exit statuses.

Implement Travis CI for build testing.

###### 1.29 - October 18, 2014
Update `IPCHandling` class to include inline comments.

Make sure all inline comments are compliant with the contribution guidelines.

###### 1.28 - October 17, 2014
Alter the `ModuleManagement` class to better conform to standards.

Adjust memory logging methods in `Logger` to better describe their function.

###### 1.27 - October 16, 2014
Use UTC timezone for all projects by default.

###### 1.26 - October 11, 2014
Move shutdown functionality to a public method.

###### 1.25 - October 9, 2014
Revamp the logging system; current logging levels:  `silent`, `info`, `stack`,
`debug`, `devel`.  Each log level is described in the
[configuration document](/docs/CONFIGURATION.md#confloglevelconf).

Automatically fork into background when in `silent` logging mode; otherwise
`silent` is equivalent to `info`.  On systems that don't support forking (ex. no
PCNTL library), `silent` will default to `info`.

`IPCHandling` related functionality is now conditionally loaded if PCNTL is
available.  Update modules to verify that `function_exists("pcntl_fork")`
evaluates to true before attempting to use `IPCHandling`.

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
