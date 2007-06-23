<?php

/**
* @author Tim Rupp
*/
class SysOps {
	/**
	* Contains a single instance of the SysOps object
	*
	* @var object
	*/
	static $instance;

	/**
	* Create and save an instance of the SysOps object
	*
	* Depending on the release of nessquik being used,
	* different libraries need to be loaded. This method
	* will create a single instance of the SysOps object.
	* The object created will be relevant to the release
	* of nessquik. This object can then be retrieved at
	* any time by re-calling the getInstance method
	*
	* @return object SysOps instance specific to the release
	*/
	static function getInstance() {
		if (empty(self::$instance)) {
			switch(_RELEASE) {
				case "fermi":
					if(!file_exists(_ABSPATH.'/lib/SysOpsFermi.php')) {
						die("Fermi SysOps class does not exist");
					}

					require_once(_ABSPATH.'/lib/SysOpsFermi.php');
					self::$instance = new SysOpsFermi;
					break;
				case "general":
				default:
					if(!file_exists(_ABSPATH.'/lib/SysOpsGeneral.php')) {
						die("General SysOps class does not exist");
					}

					require_once(_ABSPATH.'/lib/SysOpsGeneral.php');
					self::$instance = new SysOpsGeneral;
					break;
			}
		}
		return self::$instance;
	}

	/**
	* Check to see if Nessus is running
	*
	* If Nessus is not running, obviously there could be a problem
	* because no scheduled scans would be run. This will try to determine
	* if the server is running on the local host. If nessquik is configured
	* so that the scanner is on a different host from nessquik, then the
	* check will always return true because there is no good way to
	* absolutely make sure it is running on a remote system
	*
	* @return bool True on success, false on failure
	*/
	public function check_nessus() {
		/**
		* If the nessus server is not running on localhost,
		* there is no good (said fast) way to know if it is running.
		* Therefore always return success if not running on localhost
		*/
		if (	(_NESSUS_SERVER != "localhost")
			&& (_NESSUS_SERVER != "127.0.0.1")
			&& (_NESSUS_SERVER != import_var('SERVER_NAME', 'SE'))
			&& (_NESSUS_SERVER != import_var('SERVER_ADDR', 'SE')))
			return true;

		exec("ps auxw|grep nessusd|grep -v grep", $pso);

		$pso    = @preg_replace("/\s+/", " ", $pso[0]);

		$list   = explode(" ", $pso);

		$pid    = @$list[1];
		$start  = @$list[8];

		if ($pid != "") {
			return true;
		} else {
			return false;
		}
	}

	/**
	* Check if the version of nessquik is up-to-date
	*
	* Since a lot is likely to change between nessquik
	* versions, I'm including this method here so that
	* the system can be checked for a particular configuration
	* that is specific to an install of nessquik and if
	* that configuration does not exist, an error message
	* will be displayed notifying the user that they
	* are using a new version of nessquik with an old version
	* of the database
	*/
	public function check_version() {
		$success	= false;
		$db 		= nessquikDB::getInstance();
		$tpl 		= SmartyTemplate::getInstance();
		$sql	= array(
			'tables' => "SHOW TABLES FROM "._DBUSE
		);

		$stmt1 = $db->prepare($sql['tables']);
		$stmt1->execute();

		if ($stmt1->num_rows() == 0) {
			$tpl->assign("SUCCESS", "noper");
			$tpl->assign("MESSAGE", "It seems you haven't created your database yet. Use the setup file to do this.<p>");
			$tpl->assign("RETURN_LINK", "");
			$tpl->display("actions_done.tpl");
			exit;
		}

		while($row = $stmt1->fetch_row()) {
			$table = $row[0];

			if ($table == "division_group_list") {
				$success = true;
				break;
			}
		}

		if (!$success) {
			$tpl->assign("SUCCESS", "noper");
			$tpl->assign("MESSAGE", "You're trying to use the nessquik 2.5 code base with a nessquik 2.0 database.<p>"
				. "This isn't going to work. Please run the upgrade scripts before continuing."
			);
			$tpl->assign("RETURN_LINK", "");
			$tpl->display("actions_done.tpl");
			exit;
		}
	}
}

?>
