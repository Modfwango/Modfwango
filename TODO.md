* Convert flatfile configuration for UserIdentification module to inline code inside of `main.php`.
* Add events (channel/user mode, topic, public/private notice, etc).
* Implement new events into CleanLogs module.
* Add `define("__DEBUG__", false);` to main.php to allow plugins to determine if debug mode is active.
* Implement global logger API with debug and normal logging types.
