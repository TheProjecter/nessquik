<?php

/**
* @author Tim Rupp
*/
class User {
	/**
	* Contains a single instance of the User object
	*
	* @var object
	*/
	static $instance;

	/**
	* Create and save an instance of the User object
	*
	* Depending on the release of nessquik being used,
	* different libraries need to be loaded. This method
	* will create a single instance of the User object.
	* The object created will be relevant to the release
	* of nessquik. This object can then be retrieved at
	* any time by re-calling the getInstance method
	*
	* @return object User instance specific to the release
	*/
	static function getInstance() {
		if (empty(self::$instance)) {
			switch(_RELEASE) {
				case "fermi":
					if(!file_exists(_ABSPATH.'/lib/UserFermi.php')) {
						die("Fermi Users class does not exist");
					}

					require_once(_ABSPATH.'/lib/UserFermi.php');
					self::$instance = new UserFermi;
					break;
				case "general":
				default:
					if(!file_exists(_ABSPATH.'/lib/UserGeneral.php')) {
						die("General Users class does not exist");
					}

					require_once(_ABSPATH.'/lib/UserGeneral.php');
					self::$instance = new UserGeneral;
					break;
			}
		}
		return self::$instance;
	}
}


?>
