Modfwango [![Build Status](http://bit.ly/1ntkobG)](http://bit.ly/1zg30fI)
=========

<img align="right" width=135 src="http://dpr.clayfreeman.com/1cc2Y+">
Modfwango (pronounced `mahd-fuān-gogh`) is a modular socket framework written in
PHP.  This framework has been refined over the past few years for personal use,
and I decided to make it open to the public.  It is stable, clean, modular, and
object oriented.  This particular repo stemmed from the
[IRCBot-PHP](https://github.com/Modfwango/IRCBot-PHP) framework, formerly known
as Modfwango.  I decided to make the separation, because a networking framework
with reloadable modules is of more use to people than just a simple IRC bot.

Normally in PHP, classes cannot be redefined.  This limits you as a developer
because you don't have the ability to change code on-the-fly and reload it.
Modfwango solves this problem by creating randomly generated class names for
each module, every time it's loaded.  After a module is loaded, it's kept in an
array internal to the `ModuleManagement` class.  You can then decide when to
load and unload modules at will, and the `ModuleManagement` will simply either
append or truncate your module from its internal array of loaded modules.

Table of Contents
=================

* [Install](/docs/INSTALL.md):  Learn how to setup a Modfwango-based project
* [Update](/docs/UPDATE.md):  Learn how to update Modfwango core in your project
* [Configuration](/docs/CONFIGURATION.md):  Learn how to configure Modfwango
* [Change Log](/docs/CHANGELOG.md):  See all changes to Modfwango
* [Contribute](/docs/CONTRIBUTE.md):  Learn proper code submission guidelines
* [Development](/docs/DEVELOPMENT.md):  Tutorial for Modfwango development

Support
=======

For support with this framework, join IRC at `irc.freenode.org` `#modfwango`,
open a ticket, or email me using my email address on GitHub.

Licensing
=========

This work is licensed under the Creative Commons Attribution-ShareAlike 4.0
International License. To view a copy of this license, visit
http://creativecommons.org/licenses/by-sa/4.0/ or send a letter to Creative
Commons, PO Box 1866, Mountain View, CA 94042, USA.
