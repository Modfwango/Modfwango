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
git submodule add https://github.com/Modfwango/Modfwango.git .modfwango
cp .modfwango/launcher.php main.php
php main.php
```

Those commands will initialize a git repository, add Modfwango as a submodule,
provide you with a launcher for your project, and create a base directory/file
structure.
