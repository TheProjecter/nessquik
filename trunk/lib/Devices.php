<?php

/**
* @author Tim Rupp
*/
class Devices {
	/**
	* Contains a single instance of the Devices object
	*
	* @var object
	*/
	static $instance;

	/**
	* Create and save an instance of the Devices object
	*
	* Depending on the release of nessquik being used,
	* different libraries need to be loaded. This method
	* will create a single instance of the Devices object.
	* The object created will be relevant to the release
	* of nessquik. This object can then be retrieved at
	* any time by re-calling the getInstance method
	*
	* @return object Devices instance specific to the release
	*/
	static function getInstance() {
		if (empty(self::$instance)) {
			switch(_RELEASE) {
				case "fermi":
					if(!file_exists(_ABSPATH.'/lib/DevicesFermi.php')) {
						die("Fermi Devices class does not exist");
					}

					require_once(_ABSPATH.'/lib/DevicesFermi.php');
					self::$instance = new DevicesFermi;
					break;
				case "general":
				default:
					if(!file_exists(_ABSPATH.'/lib/DevicesGeneral.php')) {
						die("General Devices class does not exist");
					}

					require_once(_ABSPATH.'/lib/DevicesGeneral.php');
					self::$instance = new DevicesGeneral;
					break;
			}
		}
		return self::$instance;
	}
}


?>
