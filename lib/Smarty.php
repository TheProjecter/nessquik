<?php

require_once(_ABSPATH.'/lib/smarty/Smarty.class.php');

/**
* @author Tim Rupp
*/
class SmartyTemplate {
	/**
	* Contains a single instance of the SmartyTemplate object
	*
	* @var object
	*/
	static $instance;

	/**
	* Create and save an instance of the SmartyTemplate object
	*
	* This method will create a single instance of the SmartyTemplate
	* object. The object created will be relevant to the release
	* of nessquik. This object can then be retrieved at
	* any time by re-calling the getInstance method
	*
	* @return object SmartyTemplate instance specific to the release
	*/
	static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new Smarty;
		}
		return self::$instance;
	}
}

?>
