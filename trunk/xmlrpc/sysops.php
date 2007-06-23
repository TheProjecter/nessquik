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
			// SysOps API
			'sysops.markOnline'		=> 'this:sysops_markOnline',
			'sysops.markOffline'		=> 'this:sysops_markOffline',
			'sysops.whatsOnline'		=> 'this:sysops_whatsOnline',
			'sysops.isOnline'		=> 'this:sysops_isOnline'
		);
		$this->IXR_Server($this->methods);
	}

	private function api_key_ok($api_key) {
		$db	= nessquikDB::getInstance();
		$sql 	= "SELECT api_id FROM api_keys WHERE api_key=':1' LIMIT 1";

		$stmt	= $db->prepare($sql);
		$stmt->execute($client_key);

		if ($stmt->num_rows() < 1) {
			$this->error = new IXR_Error(403, 'Bad API key.');
			return false;
		}

		return true;
	}

	public function sysops_markOnline() {
		
	}

	public function sysops_markOffline() {

	}

	public function sysops_whatsOnline() {

	}

	public function sysops_isOnline() {

	}

	private function set_system_status($status, $client_key) {
		switch($status) {
			case "on":
			case "off":
				break;
			default:
				$status = "on";
				break;
		}
	}
}

$nessquik_xmlrpc_server = new nessquik_xmlrpc_server();

?>
