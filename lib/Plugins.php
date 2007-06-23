<?php

require_once(_ABSPATH.'/db/nessquikDB.php');

/**
* @author Tim Rupp
*/
class Plugins {
	/**
	* Profile ID of the profile that is being operated on.
	* Profile IDs are 32 character strings.
	*
	* example:
	*
	*	9a4b8cd8b91a9229e12ee05c4ed7c372
	*
	* @var string
	*/
	private $profile_id;

	/**
	* Constructor. Basic contructor that assigns a profile
	* ID to the class if it is supplied.
	*
	* @param string $profile_id ID of the profile to be
	*	tweaked in later functions. If it is specified
	*	at object creation time, the ID does not
	*	need to be specified for each of the methods
	*	that need it below.
	*/
	public function __construct($profile_id = '') {
		$this->profile_id = $profile_id;
	}

	/**
	* Add a plugin to the profile
	*
	* Plugin lists can be edited at any time to include
	* new plugins in them. This function handles adding
	* plugins to a scan profile
	*
	* @param string $plugin_type Type of the plugin.
	*	For example, 'sev','fam','plu', ...
	* @param string $plugin Value of the plugin to
	*	insert into the database. For a specific
	*	plugin it may be an ID number. For a family,
	*	it may be the name of the family, and on and on.
	* @param string $profile_id ID of the profile to add
	*	the plugin to.
	*/
	public function add_plugin($plugin_type, $plugin, $profile_id = '') {
		if ($profile_id == '') {
			$profile_id = $this->profile_id;
		}

		$db	= nessquikDB::getInstance();
		$sql	= "INSERT INTO profile_plugin_list (
				`profile_id`,
				`plugin_type`,
				`plugin`) VALUES (':1',':2',':3');";

		$stmt	= $db->prepare($sql);
		$stmt->execute($profile_id, $plugin_type, $plugin);

	}

	/**
	* Delete a specific plugin from the scan profile
	*
	* Acts as a wrapper around lower level plugin
	* removal functions. You can give this function
	* a plugin ID or a plugin's shortname and it
	* will delete it from the scan profile
	*
	* @param string $plugin Plugin ID or shortname
	* 	of the plugin that you want to remove
	* 	from the scan profile
	* @param string $profile_id ID of the profile
	*	that you want to remove the profile
	*	from. If not specified, then the one
	*	assigned to the class variable will
	*	be used
	* @return boolean True on success, false on failure
	*/
	public function delete_plugin($plugin, $profile_id = '') {
		$result = false;

		if ($profile_id == '') {
			$profile_id = $this->profile_id;
		}

		if (is_numeric($plugin)) {
			$result = $this->delete_plugin_by_pluginid($plugin, $profile_id);
		}

		if ($result === true) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Delete a plugin from the scan profile by ID
	*
	* This method will delete a plugin from the scan
	* profile based on the plugin's ID. This is a
	* skeleton function for now until code is
	* refactored in 2.6
	*
	* @param integer $plugin_id ID of the plugin to
	*	remove from the scan profile.
	*/
	public function delete_plugin_by_pluginid($plugin_id, $profile_id) {
		return true;
	}

	/**
	* Add all plugins to a scan profile
	*
	* Skeleton function for now. Not used yet
	*/
	public function add_all_plugins() {
		return true;
	}

	/**
	* Remove all the plugins from a profile
	*
	* This method will remove the plugins from the profile
	* table for the given profile. It's the equivalent of
	* assigning an empty plugin set to the scan profile
	*
	* @param string $profile_id ID of the profile to remove
	*	all the plugins from
	* @return True on successful removal, false otherwise
	*/
	public function delete_all_plugins($profile_id = '') {
		if ($profile_id == '') {
			$profile_id = $this->profile_id;
		}

		$db 	= nessquikDB::getInstance();
		$sql	= "DELETE FROM profile_plugin_list WHERE profile_id=':1';";

		$stmt	= $db->prepare($sql);
		$stmt->execute($profile_id);

		if($stmt->affected() < 0) {
			return false;
		} else {
			return true;
		}
	}
}

?>
