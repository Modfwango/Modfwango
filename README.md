Modfwango
=========

Modfwango is a modular IRC bot framework written in PHP.  This framework has been refined over the past two years for personal use, and I have just recently decided to make it open to the public.  This IRC bot framework has been used by me almost every day for the past couple of years.  It is stable, clean, modular, and object oriented.


Install
=======

This bot was tested under Ubuntu Linux and Mac OS X.  Windows compatibility is unknown, and probably unstable.  To use this framework, make sure that you have the latest version of PHP 5 CLI installed on your machine.  Configuration for this bot is inside `main.php`.  After you are done configuring the bot, just run the main file with `php main.php` and the bot will start.  You can put this into a screen just by doing `screen php main.php`.


Development
===========

###Module Management
#####Loading a Module
To load a module, just insert the following snippet of code:

`ModuleManagement::loadModule("admin/Example");`

Return Value:  `true` if the module was loaded successfully, or `false` if something went wrong.

The text `admin/Example` will load the file at `includes/modules/admin/Example.php`.

#####Unloading a Module
To unload a module, just insert the following snippet of code:

`ModuleManagement::unloadModule("admin/Example");`

Return Value:  `true` if the module was unloaded successfully, or `false` if something went wrong.

The module will still be in memory, but it will not be utilized at all by Modfwango.  Write your modules so that they get any particular module from the ModuleManagement class every time they need it so that your module won't be using stale code.

#####Reloading a Module
To reload a module, just insert the following snippet of code:

`ModuleManagement::reloadModule("admin/Example");`

Return Value:  `true` if the module was reloaded successfully, or `false` if something went wrong.

Reloading modules basically just unloads the module, and loads the module.  Refer to the previous examples for more info.

###Event Handling
#####Creating an Event
To create an event, just insert the following snippet of code:

`EventHandling::createEvent("testEvent", $this, "callbackFunction");`

Return Value:  `true` if the event was created successfully, or `false` if something went wrong.

Events use the callback function in your module to allow raw data to be preprocessed for delivery to other modules.  After the data has been preprocessed, you trigger your event in the specific modules that require it.

#####Destroying an Event
To destroy an event, just insert the following snippet of code:

`EventHandling::destroyEvent("testEvent");`

Return Value:  `true` if the event was destroyed successfully, or `false` if something went wrong.

Events can be destroyed if you no longer wish for them to be used.

#####Obtaining an Array of all Events
To get an array of all events, just insert the following snippet of code:

`EventHandling::getEvents();`

Return Value:  `array(...)` of all events.

Events can be obtained using the getEvents() function for observation.

#####Registering for an Event
To register for an event, just insert the following snippet of code:

`EventHandling::registerForEvent("name", $this, "callback", "metadata");`

Return Value:  `true` if the event registration succeeded, or `false` if something went wrong.

You can register to receive event triggers for things like Channel or Private messages.

#####Unregistering for an Event
To unregister for an event, just insert the following snippet of code:

`EventHandling::unregisterForEvent("name", $this);`

Return Value:  `true` if the event unregistration succeeded, or `false` if something went wrong.

You can unregister for events if you no longer wish to receive that event's triggers.


Support
=======

For support with this bot's framework, join our IRC channel at `irc.tinycrab.net` port `6667` channel `#modfwango`.
