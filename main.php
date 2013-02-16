<?php
	/* Show all errors. */
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	
	/* Make sure the path to the project root is alphanumeric, including the / and . characters. */
	if (!preg_match("/^[a-zA-Z0-9\\/.]+$/", dirname(__FILE__))) {
		die("The full path to this file must match this regular expression:\n^[a-zA-Z0-9\\/.]+$\n");
	}
	
	/* Define the root of the project folder. */
	define("__PROJECTROOT__", dirname(__FILE__));
	
	/* Define start time to allow some fancy uptime module features and whatnot */
	define("__STARTTIME__", time());
	
	require_once(__PROJECTROOT__."/includes/connection.php");
	require_once(__PROJECTROOT__."/includes/connectionManagement.php");
	require_once(__PROJECTROOT__."/includes/moduleManagement.php");
	require_once(__PROJECTROOT__."/includes/eventHandling.php");
	
	/* Events must be loaded first since some modules depend on them being available. */
	ModuleManagement::loadModule("events/ChannelJoinEvent");
	ModuleManagement::loadModule("events/ChannelMessageEvent");
	ModuleManagement::loadModule("events/ChannelModeEvent");
	ModuleManagement::loadModule("events/ChannelNoticeEvent");
	ModuleManagement::loadModule("events/ChannelPartEvent");
	ModuleManagement::loadModule("events/ChannelQuitEvent");
	ModuleManagement::loadModule("events/ChannelTopicEvent");
	ModuleManagement::loadModule("events/NumericEvent");
	ModuleManagement::loadModule("events/PrivateMessageEvent");
	ModuleManagement::loadModule("events/PrivateNoticeEvent");
	ModuleManagement::loadModule("events/RawEvent");
	ModuleManagement::loadModule("events/UserModeEvent");
	
	/* Now we load all the modules. */
	ModuleManagement::loadModule("admin/ChannelManagement");
	ModuleManagement::loadModule("admin/MemoryUsage");
	ModuleManagement::loadModule("admin/ModuleControl");
	ModuleManagement::loadModule("admin/Power");
	ModuleManagement::loadModule("admin/Uptime");
	ModuleManagement::loadModule("console/CleanLogs"); // This one should only be used for development.
	ModuleManagement::loadModule("internal/PingPong");
	ModuleManagement::loadModule("internal/Startup");
	ModuleManagement::loadModule("libraries/ChannelAccess");
	ModuleManagement::loadModule("libraries/Timer");
	ModuleManagement::loadModule("libraries/UserIdentification");
	
	/* Now we estabish server connection settings.
	 *	ConnectionManagement::newConnection(Connection);
	 *		Add a Connection class here so that it can be managed easily.
	 *	
	 *	new Connection();
	 *		First value is the network name.
	 *		Second value is the address to the server.  This can be IPv4, IPv6 (address has to be surrounded by [] brackets), or a hostname.
	 *		Third value is the port.
	 *		Fourth value is whether or not to use SSL for this connection.
	 *		Fifth value is whether or not to send a password when connecting.  null to not send a password, otherwise set a string value.
	 *		Sixth value is nickname.
	 *		Seventh value is username.
	 *		Eighth value is real name.
	 *		Ninth value is an array of channels to always join, regardless of the dynamic autojoin provided by the module admin/ChannelManagement.
	 *		Tenth value is the NickServ password for the account that the nickname is associated with.
	 */
	ConnectionManagement::newConnection(new Connection("TinyCrab", "irc.tinycrab.net", 6697, true, null, "Bot", "bot", "Test Bot", array("#modfwango"), "nickservpass"));
	
	/* Don't edit below this line unless you know what you're doing. */
	
	foreach (ConnectionManagement::getConnections() as $connection) {
		$connection->connect();
	}
	
	while (true) {
		foreach (ConnectionManagement::getConnections() as $connection) {
			$data = $connection->getData();
			if ($data != false) {
				EventHandling::receiveData($connection, $data);
			}
			usleep(10000);
		}
		
		foreach (EventHandling::getEvents() as $key => $event) {
			if ($key == "connectionLoopEnd") {
				foreach ($event[2] as $id => $registration) {
					EventHandling::triggerEvent("connectionLoopEnd", $id);
				}
			}
		}
	}
?>