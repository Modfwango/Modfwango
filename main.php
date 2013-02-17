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
	
	/* Define the debug constant to allow the logger to be aware of the current logging state */
	define("__DEBUG__", false);
	
	require_once(__PROJECTROOT__."/includes/configParser.php");
	require_once(__PROJECTROOT__."/includes/connection.php");
	require_once(__PROJECTROOT__."/includes/connectionManagement.php");
	require_once(__PROJECTROOT__."/includes/logger.php");
	require_once(__PROJECTROOT__."/includes/moduleManagement.php");
	require_once(__PROJECTROOT__."/includes/eventHandling.php");
	
	/* Events must be loaded first since some modules depend on them being available. */
	foreach (explode("\n", trim(file_get_contents(__PROJECTROOT__."/conf/modules.conf"))) as $module) {
		$module = trim($module);
		if (strlen($module) > 0) {
			ModuleManagement::loadModule($module);
		}
	}
	
	/* Now we estabish server connection settings from config files found in "conf/networks/" directory
	 *	ConnectionManagement::newConnection(Connection);
	 *		Add a Connection class here so that it can be managed easily.
	 *	
	 *	new Connection();
	 */
	$networks = ConfigParser::parseFiles(glob(__PROJECTROOT__."/conf/networks/*"));
	foreach ($networks as $network) {
		$network = ConfigParser::getAssoc($network);
		$network['port'] = intval($network['port']);
		$network['ssl'] = boolval($network['ssl']);
		$network['channels'] = explode(',', $network['channels']);
		ConnectionManagement::newConnection(new Connection($network['netname'], $network['address'], $network['port'], $network['ssl'], $network['pass'], $network['nick'], $network['user'], $network['realname'], $network['channels'], $network['nspass']));
	}
	
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
	
	function boolval($input) {
		if (trim($input) == "true") {
			return true;
		}
		return false;
	}
?>