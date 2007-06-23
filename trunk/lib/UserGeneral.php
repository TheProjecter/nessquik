<?php

/**
* @author Tim Rupp
*/
class UserGeneral {
	/**
	* Specifies whether the user has any clusters
	* registered to them. In 2.5 General this is
	* always false because clusters are not available
	* yet.
	*
	* @var boolean
	*/
	public $has_clusters = false;

	/**
	* Specifies whether the user has any registered
	* machines. By default this is false because
	* user accounts don't exist in 2.5 general. This
	* will become more relevant later.
	*
	* @var boolean
	*/
	public $has_registered_comps = false;

	/**
	* Constant value of the All Groups entry in the groups table
	*
	* @var string
	*/
	const ALL_GROUPS = "All Groups";

	/**
	* Returns the User ID of a given username
	*
	* Right now this is just a skeleton function to
	* support Fermi code. This will become more relevant
	* in a future release
	*
	* @param string $username Username to fetch ID for
	* @return boolean Always returns true
	*/
	public function get_id($username) {
		return true;
	}

	/**
	*
	*/
	public function get_accepted_machine_list($id) {
		return array();
	}

	/**
	*
	*/
	public function get_username() {
		return true;
	}

	public function get_email_from_uid($uid) {
		return _RECIPIENT;
	}

	public function get_vhosts($username) {
		return array();
	}

	public function get_whitelist($username) {
		return array();
	}

	public function has_whitelist($username) {
		return false;
	}

	/**
	* Check if the user has any saved scans
	*
	* If the user has any saved scans, then I need to
	* notify them of this. This method will determine
	* if the use has created any scans in the past. This
	* method is especially important on the main creation
	* page where 'saved scans' is an option that can
	* be chosen to reschedule a saved scan
	*
	* @param string $username Name of the user to check for saved scans
	* @return bool True if the user has saved scans. False otherwise.
	*/
	public function has_saved_scans($username) {
		$db = nessquikDB::getInstance();

		$sql = array(
			'saved' => "	SELECT COUNT(*) 
					FROM profile_settings 
					WHERE username=':1' 
					AND setting_type='user';",
		);

		$stmt = $db->prepare($sql['saved']);
		$stmt->execute($username);

		if ($stmt->result(0) > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Check if a group has special plugin profiles available to them
	*
	* The link for special plugin profiles should only be displayed
	* if the user does in fact have access to at least one(1) special
	* plugin profile. By giving the group ID that a user is associated
	* with, this method will determine whether or not that group, which
	* the user is a member of, has any special plugin profiles available
	* to them.
	*
	* @param integer $division_id Group ID to check for special plugin
	*	profiles for
	* @return boolean True if at least one(1) special plugin profile is
	*	available. False otherwise.
	*/
	public function has_special_plugins($division_id) {
		$db = nessquikDB::getInstance();
		$all_groups = $this->all_groups();

		$sql = array(
			'special' => "	SELECT COUNT(*) 
					FROM special_plugin_profile AS spp 
					LEFT JOIN special_plugin_profile_groups AS sppg 
					ON spp.profile_id=sppg.profile_id 
					WHERE sppg.group_id=':1' 
					OR sppg.group_id=':2';",
		);

		$stmt = $db->prepare($sql['special']);
		$stmt->execute($division_id, $all_groups);

		if ($stmt->result(0) > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Returns the group ID for the "All Groups" group
	*
	* The "All Groups" group will likely always have the
	* group ID of one(1). But since there's no way to
	* guarentee this will the use of the MySQL auto increment
	* field, it's safer just to ask the database what
	* the ID of the "All Groups" group is.
	*
	* @return integer Group ID of the "All Groups" group
	*/
	public function all_groups() {
		$db = nessquikDB::getInstance();

		$sql = array(
			'all_groups' => "SELECT group_id 
					FROM division_group_list 
					WHERE group_name=':1'
					LIMIT 1;"
		);

		$stmt = $db->prepare($sql['all_groups']);
		$stmt->execute(self::ALL_GROUPS);

		return $stmt->result(0);
	}

	public function has_clusters($username) {
		return $this->has_clusters;
	}

	public function has_registered($username) {
		return $this->has_registered_comps;
	}

	public function count_available_scanners($division_id) {
		$db = nessquikDB::getInstance();
		$all_groups = $this->all_groups();

		$sql = array (
			'scanners' => "	SELECT COUNT(*) 
					FROM scanners AS scn 
					LEFT JOIN scanners_groups AS sg 
					ON sg.scanner_id=scn.scanner_id 
					WHERE sg.group_id=':1' 
					OR sg.group_id=':2'"
		);

		$stmt = $db->prepare($sql['scanners']);
		$stmt->execute($division_id, $all_groups);

		return $stmt->result(0);
	}

	/**
	* Determine whether the user is considered an editor or not
	*
	* This is a placeholder for the moment until the authentication
	* system is in place. This returns true always because the
	* general release of nessquik has no authentication. This
	* will change of course when the auth layer is added.
	*
	* @param array $allowed_editors List of allowed editors (admins)
	* @return bool Always returns true
	*/
	public function is_editor($allowed_editors) {
		return true;

	}

	public function get_proper_name($username = '') {
		return "admin";
	}

	public function get_division_from_uid($uid) {
		return "All Groups";
	}

	public function get_division_id($division) {
		$db = nessquikDB::getInstance();

		$sql = array(
			'select' => "SELECT group_id FROM division_group_list WHERE group_name=':1';"
		);

		$stmt1 = $db->prepare($sql['select']);
		$stmt1->execute($division);

		if ($stmt1->num_rows() > 0) {
			return $stmt1->result(0);
		} else {
			return false;
		}
	}
}

?>
