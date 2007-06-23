<?php

require_once(_ABSPATH.'/lib/SysOps.php');

/**
* @author Tim Rupp
*/
class SysOpsGeneral extends SysOps {
	/**
	* Check if the pages are being served over SSL
	* This function uses the Apache server variable
	* HTTPS to determine whether SSL is being used.
	*
	* @param boolean $stop Determine whether nessquik should
	*	stop all script execution if it hits this error.
	*	If this value is false, nessquik will just return
	*	a boolean true or false specifying whether the
	*	check passed or failed.
	* @return bool True if the pages are being served over SSL
	*/
	public function check_secure($stop = false) {
		// If the user doesnt want me to check for HTTPS, then
		// just always return true (aka lie that we're using HTTPS)
		if (_CHECK_SECURE === false) {
			return true;
		}

		$result = import_var('HTTPS', 'SE');
		$result = strtolower($result);
		$result = ($result == "on") ? true : false;

		if ($stop === true) {
			if ($result === false) {
				die("You're not running nessquik over HTTPS. Please correct this");
			}
		} else {
			return $result;
		}
	}

	/**
	* Check to see if safe_mode is enabled.
	*
	* Smarty doesn't work out of the box with this enabled
	* and I'm not interested in fixing it. Someone
	* else can fix this if they're really concerned
	*
	* @param boolean $stop Determine whether nessquik should
	*	stop all script execution if it hits this error.
	*	If this value is false, nessquik will just return
	*	a boolean true or false specifying whether the
	*	check passed or failed.
	* @return boolean True if enabled, False otherwise
	*/
	public function check_safe_mode($stop = false) {
		$result = ini_get('safe_mode');

		$result = ($result == 1) ? true : false;

		if ($stop === true) {
			if ($result === true) {
				die("safe_mode is enabled in PHP. Please disable it");
			}
		} else {
			return $result;
		}
	}

	/**
	* Check if the setup directory exists
	*
	* This has the potential to be a security issue
	* after nessquik is installed, so if I check for
	* its existence, I can alert the end user
	*
	* @param boolean $stop Determine whether nessquik should
	*	stop all script execution if it hits this error.
	*	If this value is false, nessquik will just return
	*	a boolean true or false specifying whether the
	*	check passed or failed.
	* @return boolean True if the setup directory
	*	exists. False otherwise
	*/
	public function check_setup_dir($stop = false) {
		$result = false;

		$result = file_exists(_ABSPATH.'/setup/');

		if ($stop === true) {
			if ($result === true) {
				die("Your setup directory still exists. Please remove it before using nessquik");
			}
		} else {
			return $result;
		}
	}

	/**
	* Check if the upgrade directory exists
	*
	* This is another potential problem that a baddie
	* could abuse. Better off to check to see if it
	* exists and maybe stop nessquik from going any
	* further if it does.
	*
	* @param boolean $stop Determine whether nessquik should
	*	stop all script execution if it hits this error.
	*	If this value is false, nessquik will just return
	*	a boolean true or false specifying whether the
	*	check passed or failed.
	* @return boolean True if the setup directory
	*	exists. False otherwise.
	*/
	public function check_upgrade_dir($stop = false) {
		$result = false;
		$result = file_exists(_ABSPATH.'/upgrade/');

		if ($stop === true) {
			if ($result === true) {
				die("Your upgrade directory still exists. Please remove it before using nessquik");
			}
		} else {
			return $result;
		}
	}

	/**
	* Check to see if the Smarty templates cache is writable
	*
	* Smarty needs to write to this directory or else the
	* nessquik pages cant be generated. This function only
	* checks to see if the web server can write to the
	* directory. If safe_mode is enabled, this will be true
	* to an extent, but Smarty will still choke when it
	* tries to actually write to the directory. Another
	* method is included to check for safe_mode so that
	* both bases are covered.
	*
	* @param boolean $stop Determine whether nessquik should
	*	stop all script execution if it hits this error.
	*	If this value is false, nessquik will just return
	*	a boolean true or false specifying whether the
	*	check passed or failed.
	* @return boolean True if writable, False otherwise
	*/
	public function check_cache_writable($stop = false) {
		$result = false;
		$result = is_writable(_ABSPATH.'/templates_c/');

		if ($stop === true) {
			if ($result === false) {
				die("Your templates_c directory is not writable by the web server");
			}
		} else {
			return $result;
		}
	}

	/**
	* Wraps all the checks for correct install into
	* one function
	*
	* This method wraps other methods so that I
	* dont need to worry about calling the individual
	* methods. Instead I can just call one method
	* and find out if I should continue along with
	* running the scripts or stop dead in my tracks.
	* If this function returns false, then there's
	* a problem and I need to stop execution.
	*
	* @return boolean True if the install is sane,
	*	false otherwise.
	*/
	public function sanity_check() {
		
	}
}

?>
