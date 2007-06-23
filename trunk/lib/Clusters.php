<?php

/**
* @author Tim Rupp
*/
class Clusters {
	/**
	* Contains a single instance of the Clusters object
	*
	* @var object
	*/
	static $instance;

	/**
	* Create and save an instance of the Clusters object
	*
	* Depending on the release of nessquik being used,
	* different libraries need to be loaded. This method
	* will create a single instance of the Clusters object.
	* The object created will be relevant to the release
	* of nessquik. This object can then be retrieved at
	* any time by re-calling the getInstance method
	*
	* @return object Clusters instance specific to the release
	*/
	static function getInstance() {
		if (empty(self::$instance)) {
			switch(_RELEASE) {
				case "fermi":
					if(!file_exists(_ABSPATH.'/lib/ClustersFermi.php')) {
						die("Fermi Clusters class does not exist");
					}

					require_once(_ABSPATH.'/lib/ClustersFermi.php');
					self::$instance = new ClustersFermi;
					break;
				case "general":
				default:
					if(!file_exists(_ABSPATH.'/lib/ClustersGeneral.php')) {
						die("General Clusters class does not exist");
					}

					require_once(_ABSPATH.'/lib/ClustersGeneral.php');
					self::$instance = new ClustersGeneral;
					break;
			}
		}
		return self::$instance;
	}
}


?>
