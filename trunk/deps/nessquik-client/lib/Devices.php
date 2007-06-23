<?php

/**
* @author Tim Rupp
*/
class Devices {
	static $instance;

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
