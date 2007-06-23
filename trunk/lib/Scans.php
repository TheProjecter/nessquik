<?php

require_once(_ABSPATH.'/db/nessquikDB.php');

/**
* @author Tim Rupp
*/
class Scans {
	/**
	* Holds SQL that I know I'll be using repeatedly
	*
	* @var array
	*/
	private $sql;

	/**
	* Default name for a scan profile if none is specified
	*
	* @var string
	*/
	const DEFAULT_SETTING_NAME = "on-the-fly";

	/**
	* Constructor for creating new Scans objects. In
	* particular I'm creating the array of SQL that is
	* used in several functions.
	*/
	public function __construct() {
		$this->sql = array(
			'settings' => "	SELECT 	pl.profile_id,
						ust.setting_name,
						pl.date_scheduled,
						pl.date_finished,
						ust.save_scan_report,
						ust.scanner_id,
						ust.username 
					FROM profile_list AS pl 
					LEFT JOIN profile_settings AS ust 
					ON pl.profile_id=ust.profile_id 
					WHERE pl.status=':1' 
					AND pl.username=':2' 
					ORDER BY ust.setting_id ASC;",

			'results' => "	SELECT results_id 
					FROM saved_scan_results 
					WHERE profile_id = ':1' 
					ORDER BY results_id DESC 
					LIMIT 1;"
		);
	}

	/**
	* Get the list of scans of a given type for a user
	*
	* This method is a wrapper method for the different
	* types of scans that can be retrieved. This method
	* will return a list of scans for the given user
	* that have the current status that is provided.
	* If no type is provided, then by default "pending"
	* will be selected.
	*
	* @param string $type Status of scans you want to return
	* @param string $username User whose scans you want to query
	* @return array Array of scans that match the given type for
	*	the given user.
	*/
	public function get_scans($type, $username) {
		$scans = array();

		switch($type) {
			case "notready":
				$scans = $this->get_notready_scans($username);
				break;
			case "running":
				$scans = $this->get_running_scans($username);
				break;
			case "finished":
				$scans = $this->get_finished_scans($username);
				break;
			case "pending":
			default:
				$scans = $this->get_pending_scans($username);
				break;
			
		}

		return $scans;
	}

	/**
	* Return all the scans for a given type
	*
	* This method will return a list of all the scans for a given
	* type. It joins the profile list and profile settings tables
	* to return the most relevant information about scan profiles.
	*
	* @param string $type Type of scans to retrieve from the database
	* @return array Array of scans matching the given type
	*/
	public function get_all_scans($type) {
		$scans = array();

		$this->sql['settings'] = "SELECT pl.profile_id,
						ust.setting_name,
						pl.date_scheduled,
						pl.date_finished,
						ust.save_scan_report,
						ust.scanner_id,
						ust.username 
					FROM profile_list AS pl 
					LEFT JOIN profile_settings AS ust 
					ON pl.profile_id=ust.profile_id 
					WHERE pl.status=':1' 
					ORDER BY ust.setting_id ASC;";

		$scans = $this->get_scans($type, '');

		return $scans;
	}

	/**
	* Get a list of pending scans for a user
	*
	* Given a username, this method will return a list of
	* all the pending scans for that user. The profile ID
	* for the scan, along with the profile's name and the
	* date it was scheduled will be returned for each
	* pending scan.
	*
	* @param string $username Username of the user to get
	*	a list of pending scans for
	* @return array List of pending scans along with some
	*	meta data for each scan
	*/
	public function get_pending_scans($username) {
		$scans 	= array();
		$db 	= nessquikDB::getInstance();
		$stmt1	= $db->prepare($this->sql['settings']);

		if ($username == '') {
			$stmt1->execute('P');
		} else {
			$stmt1->execute('P', $username);
		}

		while ($row = $stmt1->fetch_assoc()) {
			if (($row['setting_name'] == '') || @is_null($row['setting_name'])) {
				$row['setting_name'] = self::DEFAULT_SETTING_NAME;
			}

			$scans[] = array(
				'profile_id'	=> $row['profile_id'],
				'name' 		=> $row['setting_name'],
				'user'		=> $row['username'],
				'scheduled'	=> $row['date_scheduled']
			);
		}

		return $scans;
	}

	/**
	* Get list of scans not ready to run
	*
	* Given a username, this method will return a list
	* of scans for the user that are not ready to run.
	* This method will also check to see if there are
	* currently any saved results for each not ready
	* scan. The reason for this is that you can cancel
	* a pending scan that has been rescheduled. If that
	* scan finished in the past, then it may have results
	* associated with it. If that's the case, then I'd
	* want the user to see the drop down menus for their
	* scan results even for the 'not ready' scans.
	*
	* @param string $username Username of the user to get
	*	a list of pending scans for
	* @return array List of pending scans along with some
	*	meta data for each scan
	*/
	public function get_notready_scans($username) {
		$scans	= array();

		$db 	= nessquikDB::getInstance();
		$sa 	= resultsDB::getInstance();

		$stmt1 	= $db->prepare($this->sql['settings']);
		$stmt2 	= $sa->prepare($this->sql['results']);

		$stmt1->execute('N', $username);

		while ($row = $stmt1->fetch_assoc()) {
			$scheduled 	= '';
			$results_id	= false;
			$saved_results	= false;

			if ($row['setting_name'] == '') {
				$row['setting_name'] = self::DEFAULT_SETTING_NAME;
			}

			$stmt2->execute($row['profile_id']);
			if ($stmt2->num_rows() == 1) {
				$results_id = $stmt2->result(0);
				$saved_results = true;
			}

			$scans[] = array(
				'profile_id'	=> $row['profile_id'],
				'results_id'	=> $results_id,
				'name' 		=> $row['setting_name'],
				'user'		=> $row['username'],
				'scheduled'	=> $row['date_scheduled'],
				'scanner'	=> $row['scanner_id'],
				'saved_results'	=> $saved_results
			);
		}

		return $scans;
	}

	public function get_running_scans($username) {
		$scans 	= array();

		$db 	= nessquikDB::getInstance();
		$sql = array(
			'progress' => "	SELECT attack_percent
					FROM scan_progress 
					WHERE profile_id=':1';"
		);

		$stmt1	= $db->prepare($this->sql['settings']);
		$stmt2 	= $db->prepare($sql['progress']);

		$stmt1->execute('R', $username);

		while ($row = $stmt1->fetch_assoc()) {
			$scheduled 	= '';
			$difference	= 0;

			$stmt2->execute($row['profile_id']);
			$percent_done = $stmt2->result(0);

			if ($percent_done == 0) {
				$percent_done_text = 'starting scan...';
			} else {
				$percent_done_text = "$percent_done %";
			}

			if (($row['setting_name'] == '') || @is_null($row['setting_name'])) {
				$row['setting_name'] = self::DEFAULT_SETTING_NAME;
			}

			$scheduled = $row['date_scheduled'];

			$scans[] = array(
				'profile_id'	=> $row['profile_id'],
				'name' 		=> $row['setting_name'],
				'user'		=> $row['username'],
				'progress'	=> $percent_done,
				'progress_text'	=> $percent_done_text,
				'scheduled'	=> $scheduled,
				'difference'	=> 100 - $percent_done
			);
		}

		return $scans;
	}

	public function get_finished_scans($username) {
		$scans	= array();

		$db 	= nessquikDB::getInstance();
		$sa 	= resultsDB::getInstance();

		$stmt1 = $db->prepare($this->sql['settings']);
		$stmt2 = $sa->prepare($this->sql['results']);

		$stmt1->execute('F', $username);

		while ($row = $stmt1->fetch_assoc()) {
			$results_id	= false;

			if ((@$row['setting_name'] == '') || @is_null($row['setting_name'])) {
				$row['setting_name'] = self::DEFAULT_SETTING_NAME;
			}

			if ($row['save_scan_report'] == 1) {
				/**
				* I'm doing the second query below to verify that
				* there are saved scan results in the first place.
				*
				* The scenario being that someone performs a saved 
				* scan. Then goes to the scan profile page and changes
				* the profile to not save scans. Then they go back to
				* the scans page and they will see that there are no
				* saved scans, when in reality there are.
				*
				* In any case, the delete button would blow all the
				* scan data away. I just dont want the users reporting
				* that changing that setting makes their data go bye bye;
				* because it doesnt.
				*/
				$stmt2->execute($row['profile_id']);
				if ($stmt2->num_rows() == 1) {
					$saved_results 	= 1;
					$results_id	= $stmt2->result(0);
				} else {
					$saved_results 	= 0;
				}
			} else {
				$saved_results = 0;
			}

			$scans[] = array(
				'profile_id'	=> $row['profile_id'],
				'results_id'	=> $results_id,
				'name' 		=> $row['setting_name'],
				'user'		=> $row['username'],
				'finished'	=> ($row['date_finished']) ? $row['date_finished'] : "never",
				'saved_results'	=> $saved_results,
				'scanner'	=> $row['scanner_id'],
				'saved'		=> $row['save_scan_report']
			);
		}

		return $scans;
	}

	public function count_scans($username, $type) {
		$db = nessquikDB::getInstance();

		$sql = array(
			'user' => "SELECT COUNT(status) FROM profile_list WHERE status=':1' AND username=':2';",
			'nouser' => "SELECT COUNT(status) FROM profile_list WHERE status=':1';"
		);

		switch($type) {
			case "N":
			case "R":
			case "P":
			case "F":
				break;
			default:
				$type = "P";
				break;
		}
	
		if ($username == '') {
			$stmt = $db->prepare($sql['nouser']);
			$stmt->execute($type);
		} else {
			$stmt = $db->prepare($sql['user']);
			$stmt->execute($type, $username);
		}

		$result = (string)$stmt->result(0);
		$result = substr($result,0,14);

		return number_format($result);
	}

	public function count_not_running_scans($username = '') {
		return $this->count_scans($username, 'N');
	}

	public function count_pending_scans($username = '') {
		return $this->count_scans($username, 'P');
	}

	public function count_running_scans($username = '') {
		return $this->count_scans($username, 'R');
	}

	public function count_finished_scans($username = '') {
		return $this->count_scans($username, 'F');
	}

	public function remove_scan_profile($profile_id) {
		$this->delete_profile($profile_id);
		$this->delete_machine_list($profile_id);
		$this->delete_plugin_list($profile_id);
		$this->delete_settings($profile_id);
		$this->delete_scan_progress($profile_id);
		$this->delete_recurrence($profile_id);
		$this->delete_saved_scan_results($profile_id);

		return true;
	}

	private function delete_profile($profile_id) {
		$db = nessquikDB::getInstance();
		$sql = "DELETE FROM profile_list WHERE profile_id=':1';";

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id);
	}

	private function delete_machine_list($profile_id) {
		$db = nessquikDB::getInstance();
		$sql = "DELETE FROM profile_machine_list WHERE profile_id=':1';";

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id);
	}

	private function delete_plugin_list($profile_id) {
		$db = nessquikDB::getInstance();
		$sql = "DELETE FROM profile_plugin_list WHERE profile_id=':1';";

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id);
	}

	private function delete_settings($profile_id) {
		$db = nessquikDB::getInstance();
		$sql = "DELETE FROM profile_settings WHERE profile_id=':1';";

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id);
	}

	private function delete_scan_progress($profile_id) {
		$db = nessquikDB::getInstance();
		$sql = "DELETE FROM scan_progress WHERE profile_id=':1';";

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id);
	}

	private function delete_recurrence($profile_id) {
		$db = nessquikDB::getInstance();
		$sql = "DELETE FROM recurrence WHERE profile_id=':1'";

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id);
	}

	private function delete_saved_scan_results($profile_id) {
		$db = resultsDB::getInstance();
		$sql = "DELETE FROM saved_scan_results WHERE profile_id=':1'";

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id);
	}
}

?>
