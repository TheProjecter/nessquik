<?php

// Used for including files
if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(__FILE__)));
}

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/lib/IXR_Library.php');
require_once(_ABSPATH.'/lib/functions.php');
require_once(_ABSPATH.'/db/nessquikDB.php');

/**
* @author Tim Rupp
*/
class nessquik_xmlrpc_server extends IXR_Server {
	/**
	* Contains the methods that can be called
	* (Defines the API)
	*
	* @var array
	*/
	public $methods;

	/**
	* Constructor. Defines the API and starts the server
	*
	* In the constructor, the API for the server is defined
	* by placing method names inside the method array. These
	* methods must then be defined later in this class. See
	* the format of the method for clues on how to add more.
	*/
	public function __construct() {
		$this->methods = array(
			// Jobs API
			'jobs.getMachines' 		=> 'this:jobs_getMachines',
			'jobs.getAllPlugins'		=> 'this:jobs_getAllPlugins',
			'jobs.getProfilePlugins'	=> 'this:jobs_getProfilePlugins',
			'jobs.getProfileSettings'	=> 'this:jobs_getProfileSettings',
			'jobs.getPendingProfileIds'	=> 'this:jobs_getPendingProfileIds',
			'jobs.getStatus'		=> 'this:jobs_getStatus',
			'jobs.getCancel'		=> 'this:jobs_getCancel',
			'jobs.getCountRunning'		=> 'this:jobs_getCountRunning',
			'jobs.getPluginsBySeverity'	=> 'this:jobs_getPluginsBySeverity',
			'jobs.getPluginsByFamily'	=> 'this:jobs_getPluginsByFamily',
			'jobs.getSpecialProfileItems'	=> 'this:jobs_getSpecialProfileItems',
			'jobs.setResetCancel'		=> 'this:jobs_setResetCancel',
			'jobs.setProgress'		=> 'this:jobs_setProgress',
			'jobs.setStatus'		=> 'this:jobs_setStatus',
			'jobs.setFinishedDate'		=> 'this:jobs_setFinishedDate',
			'jobs.saveReport'		=> 'this:jobs_saveReport',
			'jobs.emailResults'		=> 'this:jobs_emailResults',

			// FNAL specific
			'jobs.addExemption'		=> 'this:jobs_addExemption'
		);

		parent::__construct($this->methods);
	}

	/**
	* Check to see if the client key exists
	*
	* Checks to see if the client key exists in the database
	* before allowing further commands to continue. This is
	* needed for some semi-privileged code execution which
	* involves updating user profiles or scans.
	*
	* @param string $client_key Client key that is to be checked
	* @return bool API Error and False on failure, true on success
	*/
	private function client_key_ok($client_key) {
		$db	= nessquikDB::getInstance();
		$sql 	= "SELECT scanner_id FROM scanners WHERE client_key=':1' LIMIT 1";

		$stmt	= $db->prepare($sql);
		$stmt->execute($client_key);

		if ($stmt->num_rows() < 1) {
			$this->error = new IXR_Error(403, 'Bad client key.');
			return false;
		}

		return true;
	}

	/**
	* Check if a key is privileged
	*
	* Privileged keys are used for commands that access
	* or update database tables above and beyond the normal
	* scope of the API. For instance adding exemptions to
	* our exemption database would require more privilege
	* than being able to return scan results, because we
	* don't offer the ability to the end users to add their
	* own exemptions.
	*
	* @param string $client_key Client key to check if privileged
	*/
	private function client_key_privileged($client_key) {
		$db = nessquikDB::getInstance();
		$sql = array(
			'select' => "SELECT privileged FROM scanners WHERE client_key=':1';"
		);

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($client_key);

		if ($stmt->num_rows() < 1) {
			$this->error = new IXR_Error(404, 'Client key not authorized');
			return false;
		}
	}

	/**
	* Check if client key can scan a profile
	*
	* Just because the client key is valid doesnt mean it
	* should be allowed to scan a given profile. The profile
	* settings contain a scanner_id field. This field
	* can be associated with the scanner ID in the scanners
	* table, and from there I can check the client_key. If
	* The profile ID is associated with the correct client_key
	* of the scanner, then the scan can take place
	*
	* @param string $client_key Client key to check against profile
	* @param string $profile_id Profile ID to check against client_key
	* @return bool True on success, False and API error on failure
	*/
	private function client_key_can_scan_profile($client_key, $profile_id) {
		$db	= nessquikDB::getInstance();
		$sql 	= "	SELECT scn.scanner_id 
				FROM scanners AS scn 
				LEFT JOIN profile_settings AS usr 
				ON scn.scanner_id=usr.scanner_id 
				WHERE scn.client_key=':1' 
				AND usr.profile_id=':2';";

		$stmt = $db->prepare($sql);
		$stmt->execute($client_key, $profile_id);

		if ($stmt->num_rows() < 1) {
			$this->error = new IXR_Error(404, 'Client key not authorized for profile ID');
			return false;
		}

		return true;
	}

	/**
	* Get the username from the profile
	*
	* Usernames are, for the moment, stored in the profile. This
	* will eventually change when the authentication system is in
	* place and the user_id will be stored instead. This function
	* will return the user name of a given profile_id
	*
	* @param string $profile_id Profile ID for profile to get username from
	* @return string Username for profile
	*/
	private function username_from_profile($profile_id) {
		$db	= nessquikDB::getInstance();
		$sql 	= "SELECT username FROM profile_list WHERE profile_id=':1'";
		$stmt	= $db->prepare($sql);

		$stmt->execute($profile_id);
		return $stmt->result(0);
	}

	/**
	* Fetch and decode machines associated with a profile
	*
	* Multiple types of devices including clusters, whitelist entries,
	* registered comps, etc can be included in a scan profile. Instead
	* of putting the code to decode these devices in each nessquik-client,
	* it is instead included here.
	*
	* This function will fetch the list of devices associated with a
	* scan profile, and return a list of IP addresses that can be
	* put in a nessusrc file.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client Key of the nessquik-scanner doing the query
	*	1 - Profile ID of scan to check to see if cancelled
	* @return array List of IPs that can be placed in a machine list
	*	file for nessus
	*/
	public function jobs_getMachines($params) {
		require_once(_ABSPATH.'/lib/ScanMaker.php');

		$db		= nessquikDB::getInstance();
		$client_key	= $params[0];
		$profile_id	= $params[1];
		$result		= array();

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		$result = ScanMaker::getMachines($profile_id);

		return $result;
	}

	/**
	* Get a list of all plugins
	*
	* This function will query the plugins database and return
	* an array that is indexed by plugin ID. The value of each
	* entry in the array will be 'no'. The array that is returned
	* can be looped through right away to create the plugin list
	* for a nessusrc file.
	*
	* @return array Array of plugin IDs with the value of each entry set to 'no'
	*/
	public function jobs_getAllPlugins() {
		$db	= nessquikDB::getInstance();
		$set	= array();
		$sql 	= "SELECT pluginid FROM plugins ORDER BY pluginid ASC;";

		$stmt = $db->prepare($sql);
		$stmt->execute();

		while($row = $stmt->fetch_assoc()) {
		        $set[$row['pluginid']] = 'no';
		}

		return $set;
	}

	/**
	* Get plugins associated with a profile
	*
	* To create a correct nessusrc file, the rc file should
	* contain a list of the plugins that will be used in
	* the scan. This method will return a list of all the
	* plugins that will be used so the correct list in the
	* nessusrc file can be made
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client Key of the nessquik-scanner doing the query
	*	1 - Profile ID of scan to check to see if cancelled
	*	2 - Type of plugin to select from the profile plugin list table
	* @return array List of plugins associated with the given profile ID
	*	that are of the given type
	*/
	public function jobs_getProfilePlugins($params) {
		$db		= nessquikDB::getInstance();
		$client_key	= $params[0];
		$profile_id	= $params[1];
		$plugin_type	= $params[2];
		$results	= array();
		$sql 		= "SELECT plugin_type,plugin FROM profile_plugin_list WHERE profile_id=':1' AND plugin_type=':2';";
		$stmt		= $db->prepare($sql);

		$stmt->execute($profile_id, $plugin_type);

		while($row = $stmt->fetch_assoc()) {
			$results[] = $row['plugin'];
		}

		return $results;
	}

	/**
	* Retrieve settings for a profile
	*
	* Return the settings in the profile_settings table that
	* are needed during the scan creation. See the SQL
	* statement in this method for a list of the fields
	* that are returned.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client Key of the nessquik-scanner doing the query
	*	1 - Profile ID of scan to pull settings for
	* @return array Array of profile settings
	*/
	public function jobs_getProfileSettings($params) {
		$db		= nessquikDB::getInstance();
		$client_key	= $params[0];
		$profile_id	= $params[1];
		$sql = "SELECT 	report_format,
				ping_host_first,
				alternative_cgibin_list,
				save_scan_report,
				alternative_email_list,
				custom_email_subject,
				port_range
			FROM profile_settings 
			WHERE profile_id=':1'
			LIMIT 1;";

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id);

		return $stmt->fetch_assoc();
	}

	/**
	* Get list of profile IDs for profiles that need to be run
	*
	* When the scan-runner kicks off, it selects a list of all
	* the profile IDs for profiles that are assigned to it that
	* have a pending status. This list is sorted by the date
	* the scan was scheduled. The scan-runner will then determine
	* how many scans it is allowed to start, and use the profile
	* IDs that it gets from this method to start more scans
	* using the scan-maker script.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client Key of the nessquik-scanner doing the query
	*	1 - Maximum number of scans that are allowed to be running
	* @return array Array of profile IDs
	*/
	public function jobs_getPendingProfileIds($params) {
		$db		= nessquikDB::getInstance();
		$client_key	= $params[0];
		$scan_limit	= $params[1];
		$profile_ids	= array();
		$sql 		= array(
			'scanner_id' => "SELECT scanner_id FROM scanners WHERE client_key=':1' LIMIT 1",
			'profile_ids' => "SELECT pl.profile_id 
				FROM profile_list AS pl 
				LEFT JOIN profile_settings AS usr 
				ON pl.profile_id=usr.profile_id 
					LEFT JOIN scanners AS scn 
					ON usr.scanner_id=scn.scanner_id 
				WHERE scn.scanner_id=':1' 
				AND pl.status='P' 
				AND pl.date_scheduled <= CURRENT_TIMESTAMP
				ORDER BY pl.date_scheduled ASC 
				LIMIT :2;"
		);

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		if (!is_numeric($scan_limit)) {
			$this->error = new IXR_Error(404, 'Scan limit not a valid number');
			return false;
		} else {
			// If a valid number was sent, make sure the number is
			// positive and greater than 0
			if ($scan_limit < 0) {
				$scan_limit = 1;
			} else {
				$scan_limit = abs($scan_limit);
			}
		}

		$stmt1 = $db->prepare($sql['scanner_id']);
		$stmt2 = $db->prepare($sql['profile_ids']);

		$stmt1->execute($client_key);
		$scanner_id = $stmt1->result(0);

		$stmt2->execute($scanner_id, $scan_limit);

		while($row = $stmt2->fetch_assoc()) {
			$profile_ids[] = $row['profile_id'];
		}

		return $profile_ids;
	}

	/**
	* Get the status of a profile
	*
	* This method will return the status for a given profile ID
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client Key of the nessquik-scanner doing the query
	*	1 - Profile ID of profile to get status of
	* @return string/bool Status is returned on success, false is
	*	returned otherwise
	*/
	public function jobs_getStatus($params) {
		$db		= nessquikDB::getInstance();
		$client_key	= $params[0];
		$profile_id	= $params[1];
		$sql 		= "SELECT status FROM profile_list WHERE profile_id=':1'";

		$stmt 		= $db->prepare($sql);
		$stmt->execute($profile_id);

		if ($stmt->num_rows() > 0) {
			return $stmt->result(0);
		} else {
			return false;
		}
	}

	/**
	* Check if job was cancelled
	*
	* Running jobs can be cancelled using the proc_terminate
	* feature of PHP. This function will check whether a scan
	* was cancelled in the web gui and will tell the scan-maker
	* if it was so that the scan will be killed.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Profile ID of scan to check to see if cancelled
	* @return char Y on true, N on false
	*/
	public function jobs_getCancel($params) {
		$db		= nessquikDB::getInstance();
		$cancel		= array();
		$profile_id	= $params;
		$sql		= "SELECT cancel FROM profile_list WHERE profile_id=':1'";
		$stmt		= $db->prepare($sql);
		$stmt->execute($profile_id);

		$cancel = array(
			'cancel' => $stmt->result(0)
		);

		return $cancel;
	}

	/**
	* Count the number of running scans on a scanner
	*
	* Scanners should check to see how many scans are currently
	* running before they schedule any more. This method only
	* does a database check to find the number of running scans
	* it will not actually count processes on a system.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client key of the scanner to get a count for
	* @return integer Number of scans currently running
	*/
	public function jobs_getCountRunning($params) {
		$db		= nessquikDB::getInstance();
		$count		= 0;
		$client_key	= $params;

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		$sql = array(
			'scanners'=> "SELECT scanner_id FROM scanners WHERE client_key=':1'",
			'count' => "	SELECT count(pl.status) 
					FROM profile_settings AS usrs 
					LEFT JOIN profile_list AS pl
					ON pl.profile_id = usrs.profile_id
					WHERE pl.status='R'
					AND usrs.scanner_id=':1'"
		);

		$stmt1 = $db->prepare($sql['scanners']);
		$stmt2 = $db->prepare($sql['count']);

		$stmt1->execute($client_key);

		while($row = $stmt1->fetch_assoc()) {
			$scanner_id = $row['scanner_id'];
			$stmt2->execute($scanner_id);
			$count += $stmt2->result(0);
		}

		return $count;
	}

	/**
	* Wrapper to only pull plugins by severity
	*
	* This method just wraps around the jobs_getPluginsByType
	* method. It explicitly will pull only plugins for severities
	*
	* @param string $params Get plugins for this severity
	* @return array Array of plugins for the given severity
	* @see jobs_getPluginsByType
	*/
	public function jobs_getPluginsBySeverity($params) {
		$plugins = $this->jobs_getPluginsByType('sev', $params);

		return $plugins;
	}

	/**
	* Wrapper to only pull plugins by family
	*
	* This method just wraps around the jobs_getPluginsByType
	* method. It explicitly will pull only plugins for families
	*
	* @param string $params Get plugins for this family
	* @return array Array of plugins for the given family
	* @see jobs_getPluginsByType
	*/
	public function jobs_getPluginsByFamily($params) {
		$plugins = $this->jobs_getPluginsByType('fam', $params);

		return $plugins;
	}

	/**
	* Return plugin IDs for a given type
	*
	* This is a general method that is to be wrapped by other
	* methods that implement selecting plugins by the different
	* types.
	*
	* @param string $type Type of plugins to return. At this
	*	point in time, the valid values are 'sev' and 'fam'
	* 	for 'severity' and 'family' respectively
	* @param string $type_val Value to supply for the given type.
	*	For example it could be the name of a family, or the
	*	name of a secerity.
	* @return array List of plugin IDs associated with the given
	*	value for the given type
	* @see jobs_getPluginsBySeverity
	* @see jobs_getPluginsByFamily
	*/
	private function jobs_getPluginsByType($type, $type_val) {
		$db		= nessquikDB::getInstance();
		$plugins	= array();
		$sql		= "SELECT pluginid FROM plugins WHERE :1=':2' ORDER BY pluginid ASC;";

		/**
		* Dunno how I missed this bug, apparently 'fam'
		* is not a field in the table. It's 'family'
		*/
		switch($type) {
			case "fam":
				$type = "family";
				break;
		}

		$stmt		= $db->prepare($sql);
		$stmt->execute($type, $type_val);

		while($row = $stmt->fetch_assoc()) {
			$plugins[] = $row['pluginid'];
		}

		return $plugins;
	}

	/**
	* Get the special plugin profile items for a scan profile
	*
	* Scan profiles can have plugin profiles saved with them.
	* When parsing through the plugins list, these plugin
	* profiles will need to be broken apart and evaluated
	* down to their most basic parts (getting the plugins
	* for a family or severity that is part of the plugin
	* profile for example).
	*
	* This method will just return the top-most list of
	* items in a special plugin profile. It will not get
	* the final list of plugin IDs unless of course your
	* plugin profile consists entirely of individual plugins
	*
	* @param string $params Profile ID to get plugin profile items for
	* @return array Array of all the items is returned with
	*	the expectation that further sub-processing of the
	*	items may need to take place (Like breaking down
	*	severities into individual plugins).
	*/
	public function jobs_getSpecialProfileItems($params) {
		$db		= nessquikDB::getInstance();
		$profile_id	= $params;
		$sql 		= "SELECT plugin_type,plugin FROM special_plugin_profile_items WHERE profile_id=':1'";
		$stmt		= $db->prepare($sql);
		$result		= array();

		$stmt->execute($profile_id);

		while($row = $stmt->fetch_assoc()) {
			$result[] = array(
				'plugin_type'	=> $row['plugin_type'],
				'plugin'	=> $row['plugin']
			);
		}

		return $result;
	}

	/**
	* Reset the cancel flag after scan-maker stops the scan
	*
	* To stop running scans, nessquik makes use of a flag in the
	* database that, if set, will tell the scan-maker script to
	* call proc_terminate on the process it is currently running.
	* The Nessus scan *should* stop once this happens. This function
	* will reset the flag after the scan-maker has stopped the
	* scan.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client key of the scanner
	*	1 - Profile ID to reset
	* @return boolean True on success, IXR_Error on false
	*/
	public function jobs_setResetCancel($params) {
		$db		= nessquikDB::getInstance();
		$client_key	= $params[0];
		$profile_id	= $params[1];
		$sql		= "UPDATE profile_list SET status='N',cancel='N' WHERE profile_id=':1'";
		$stmt		= $db->prepare($sql);

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}
		if (!$this->client_key_can_scan_profile($client_key, $profile_id)) {
			return $this->error;
		}

		$stmt->execute($profile_id);

		return true;
	}

	/**
	* Set the progress of a scan
	*
	* To update the progress bar shown through the nessquik
	* GUI, something needs to be reading the Nessus output
	* and trying to figure out what the current progress
	* should be set to. After that calculation is made, the
	* client can call this method to update the database
	* table that is read by the GUI to update the progress bar.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client key of the scanner to get a count for
	*	1 - Profile ID to set progress of
	*	2 - Portscan progress to set the scan profile to
	*	3 - Attack progress to set the scan profile to
	* @return True on successful progress update. IXR_Error
	*	on failure
	*/
	public function jobs_setProgress($params) {
		$db			= nessquikDB::getInstance();
		$client_key 		= $params[0];
		$profile_id		= $params[1];
		$portscan_progress	= $params[2];
		$attack_progress	= $params[3];
		$sql 			= "UPDATE scan_progress SET portscan_percent=':1',attack_percent=':2' WHERE profile_id=':3';";
		$stmt 			= $db->prepare($sql);

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		if (!$this->client_key_can_scan_profile($client_key, $profile_id)) {
			return $this->error;
		}

		$stmt->execute($portscan_progress, $attack_progress, $profile_id);

		return true;
	}

	/**
	* Set a scan's status
	*
	* This method allows you to set the status of a scan
	* from a particular status to another particular status.
	* The status that is being set should be one of the
	* four (4) valid status'
	*
	*	N - Not Ready to Run
	*	P - Pending
	*	R - Running
	*	F - Finished
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client key of the scanner
	*	1 - Profile ID to set the status of
	*	2 - Set the status from this...
	*	3 - ...to this
	* @return True on successful progress update. IXR_Error
	*	on failure
	*/
	public function jobs_setStatus($params) {
		$db		= nessquikDB::getInstance();
		$client_key 	= $params[0];
		$profile_id	= $params[1];
		$from_status	= $params[2];
		$to_status	= $params[3];

		$sql = "UPDATE profile_list 
			SET status=':1' 
			WHERE profile_id=':2' 
			AND status=':3';";

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		if (!$this->client_key_can_scan_profile($client_key, $profile_id)) {
			return $this->error;
		}

		$stmt = $db->prepare($sql);

		$stmt->execute($to_status, $profile_id, $from_status);

		return true;
	}

	/**
	* Set a scan's finish date
	*
	* After a scan has finished running, you may want
	* to set the date and time it finished running in
	* the database. This method will set that value in
	* the database. The date should be formatted according
	* to the MySQL datetime format. This can be accomplished
	* using the following strftime format.
	*
	*	strftime("%Y-%m-%d %T", time());
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client key of the scanner
	*	1 - Profile ID to set the finished date of
	*	2 - Date, in MySQL datetime format, of when
	*	    the scan finished running
	* @return True on successful progress update. IXR_Error
	*	on failure
	*/
	public function jobs_setFinishedDate($params) {
		$db		= nessquikDB::getInstance();
		$client_key 	= $params[0];
		$profile_id	= $params[1];
		$date		= $params[2];
		$sql 		= "UPDATE profile_list SET date_finished=':1' WHERE profile_id=':2';";
		$stmt		= $db->prepare($sql);

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		if (!$this->client_key_can_scan_profile($client_key, $profile_id)) {
			return $this->error;
		}

		$stmt->execute($date, $profile_id);

		return true;
	}

	/**
	* Save a scan report to the database
	*
	* After a scan has finished running, nessquik will check
	* to see if the user wanted to save the results to the
	* database. By default, in 2.5, this functionality is
	* turned on. It's become best practice to save the results
	* of scans to the database, otherwise debugging nessquik
	* and using some of it's more advanced options becomes
	* impractical.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client key of the scanner
	*	1 - Profile ID to save the results under
	*	2 - The date, in MySQL datetime format, that the
	*	    results were saved to the database
	*	3 - The full scan results to save
	* @return True on successful progress update. IXR_Error
	*	on failure
	*/
	public function jobs_saveReport($params) {
		$db		= resultsDB::getInstance();
		$client_key 	= $params[0];
		$profile_id	= $params[1];
		$saved_on	= $params[2];
		$results	= $params[3];
		$username	= $this->username_from_profile($profile_id);

		$sql = "INSERT INTO saved_scan_results (
				`profile_id`,
				`username`,
				`saved_on`,
				`scan_results`) 
			VALUES (':1',':2',':3',\":4\");";

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		if (!$this->client_key_can_scan_profile($client_key, $profile_id)) {
			return $this->error;
		}

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id,$username,$saved_on,$results);

		if (defined('_USE_RECORD_DB')) {
			if (_USE_RECORD_DB === true) {
				$this->jobs_saveHistoricReport($params);
			}
		}

		return true;
	}

	/**
	* Save a historic copy of the scan results
	*
	* Due to a partial request/oh my god what were we thinking,
	* we decided to add a historic scan results database so that
	* if needed in the future, we can reference copies of any and
	* all scans run by users even if the user deleted the scan
	* results. This method will insert the historic copies of the
	* scan data.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client key of the scanner
	*	1 - Profile ID to save the results under
	*	2 - The date, in MySQL datetime format, that the
	*	    results were saved to the database
	*	3 - The full scan results to save
	* @return True on successful progress update. IXR_Error
	*	on failure
	*/
	public function jobs_saveHistoricReport($params) {
		$db		= historyDB::getInstance();
		$client_key 	= $params[0];
		$profile_id	= $params[1];
		$saved_on	= $params[2];
		$results	= $params[3];
		$username	= $this->username_from_profile($profile_id);

		$sql = "INSERT INTO historic_saved_scan_results (
				`profile_id`,
				`username`,
				`saved_on`,
				`scan_results`) 
			VALUES (':1',':2',':3',\":4\");";

		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		if (!$this->client_key_can_scan_profile($client_key, $profile_id)) {
			return $this->error;
		}

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id,$username,$saved_on,$results);

		return true;
	}

	/**
	* Emails the results of a scan to the scheduler
	*
	* By default, all nessquik scan results are emailed
	* to the person who created the scan and, optionally,
	* to a list of recipients who the scan owner wants
	* to inform.
	*
	* This method takes several arguments and uses them
	* to craft an email to send.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client key of the scanner
	*	1 - Profile ID associated with the scan
	*	2 - Array of recipients to send the scan to
	*	    in addition to sending the scan to the user
	*	    who scheduled it
	*	3 - The subject line of the email
	*	4 - The full, formatted, Nessus scan results you
	*	    want to send to the recipients.
	*	5 - The format of the email being sent. Usually
	*	    this is also the format of the scan results.
	*	    eg. html if you want to send HTML results in
	*	    your email.
	* @return True on successful progress update. IXR_Error
	*	on failure
	*/
	public function jobs_emailResults($params) {
		require_once(_ABSPATH.'/lib/User.php');

		$_usr		= User::getInstance();
		$client_key	= $params[0];
		$profile_id	= $params[1];
		$recipients	= $params[2];
		$subject	= $params[3];
		$output		= $params[4];
		$format		= $params[5];
		$username	= $this->username_from_profile($profile_id);
		$email 		= $_usr->get_email_from_uid($username);
		
		if (!$this->client_key_ok($client_key)) {
			return $this->error;
		}

		if (!$this->client_key_can_scan_profile($client_key, $profile_id)) {
			return $this->error;
		}

		send_email($email, $recipients, $subject, $output, $format);

		return true;
	}

	/**
	* Add an exemption to the database
	*
	* On site we maintain an exemptions table for use
	* when requesting, for instance, web exemptions
	* in the border router ACL. This method updates
	* the exemption table so that if a person goes to
	* request a new exemption, their scan results will
	* have been added to the table already, and they
	* can proceed with the exemption request.
	*
	* @param array $params Array of parameters sent to the function
	*	0 - Client key of the scanner
	*	1 - Profile ID associated with the scan
	*	2 - Username of the person who performed the
	*	    scan. This is stored in the database for
	*	    reference later if needed.
	*	3 - Duration, in seconds, of the scan
	* @return True on successful progress update. IXR_Error
	*	on failure
	*/
	public function jobs_addExemption($params) {
		$client_key	= $params[0];
		$profile_id	= $params[1];
		$username	= $params[2];
		$duration	= $params[3];

		if (!$this->client_key_privileged($client_key)) {
			return $this->error;
		}

		$ex 		= exemptDB::getInstance();
		$machine_list	= ScanMaker::getMachines($profile_id);

		$sql	= array (
			'sel_exemptions' => "	SELECT urn 
						FROM scan 
						WHERE user=':1' 
						AND latest='True';",

			'upd_exemptions' => "	UPDATE scan 
						SET latest=':1' 
						WHERE urn=':2';",

			'ins_exemption' => "	INSERT INTO scan (
							`ip`,
							`scandate`,
							`duration`,
							`latest`,
							`dns`,
							`user`,
							`scanner`) 
						VALUES (':1',':2',':3','True',':4',':5','sham-ness');"
		);

		$stmt1 = $ex->prepare($sql['sel_exemptions']);
		$stmt2 = $ex->prepare($sql['upd_exemptions']);
		$stmt3 = $ex->prepare($sql['ins_exemption']);

		// Select the latest exemption for a user
		$stmt1->execute($username);

		// Set the latest exemption equal to false
		while ($row = $stmt1->fetch_assoc()) {
			$urn = $row['urn'];
			$stmt2->execute('False',$urn);
		}

		/**
		* For each machine, not cidr or range, insert that
		* as an entry into the exempt database
		*/
		foreach($machine_list as $key => $val) {
			if (!is_ip($val)) {
				continue;
			}

			$date	= strftime("%Y-%m-%d",time());

			// hostname is one of the database fields, so get it from DNS
			$host 	= gethostbyaddr($val);

			/**
			* Insert the exemption. Default to 'True' for latest
			* because all previous 'True' were set to 'False'	
			*/
			$stmt3->execute($val,$date,$duration,$host,$username);
		}

		return true;
	}
}

$nessquik_xmlrpc_server = new nessquik_xmlrpc_server();

?>
