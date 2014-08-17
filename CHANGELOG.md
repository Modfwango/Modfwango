Change Log
==========

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
