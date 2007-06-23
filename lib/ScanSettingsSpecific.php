<?php

require_once(_ABSPATH.'/lib/ScanSettings.php');

/**
* @author Tim Rupp
*/
class ScanSettingsSpecific extends ScanSettings {
	/**
	* The settings that can be configured per-scan
	*
	* @var array
	*/
	private $settings;

	/**
	* Constructor, instantiates a new object for configuring
	* specific scan settings
	*
	* @param string $profile_id Profile ID of the profile
	*	that is being configured
	*/
	public function __construct($profile_id = false) {
		$this->settings = array(
			'scan_name'			=> '',
			'short_plugin_listing'		=> '',
			'ping_host_first'		=> '',
			'save_scan_report'		=> '',
			'report_format'			=> '',
			'port_range'			=> '',
			'recurring'			=> '',
			'custom_email_subject'		=> '',
			'alternative_email_list'	=> '',
			'alternative_cgibin_list'	=> ''
		);
	}

	/**
	* Prep the email related settings for the database
	*
	* This method is a skeleton method right now and
	* is not used. In the future it will be used to
	* prep the email-ish settings before they're inserted
	* into the database
	*/
	public function update_alternate_email($email) {
		$settings['alternative_email_list'] 	= make_alternate_email_to_list($alternate_email_to);
		$settings['custom_email_subject']	= substr(import_var('custom_email_subject', 'P', 'email_subject'), 0, 128);
	}

	/**
	* Override PHP's default set handler
	*
	* I'm overriding PHP's default set handler here to
	* account for the need to format the list of alt email
	* and cgibin lists. The new setter specifically picks
	* out those two variables and formats the data in them
	* before they can be tweaked.
	*
	* @param string $name Class variable to set
	* @param mixed $value Value to set the class variable to
	*/
	public function __set($name, $value) {
		if ($name == "alternative_email_list") {
			$value = $this->make_alternate_email_to_list($value);
		} else if ($name == "alternative_cgibin_list") {
			$value = $this->make_alternate_cgibin_list($value);
		} else {
			$key = "_".$name;
			$this->$key = $value;
		}
	}

	/**
	* Update a profiles' settings in the database
	*
	* Updates all the settings in the database that have
	* changed from their default values since the settings
	* object was created.
	*/
	public function update() {
		$db	= nessquikDB::getInstance();
		$sql 	= "UPDATE profile_settings SET :1=':2' WHERE profile_id='".$this->profile_id."' AND setting_type='user'";

		$stmt	= $db->prepare($sql);

		// Update all the fields in the database
		$stmt->execute('setting_name', $this->scan_name);
	}

	/**
	* Create alternate cgi-bin string
	*
	* Just like the alternate email list, the alternate cgi-bin list
	* is stored as a comma separated string in the database. This
	* function converts the array that the cgi's are gathered in, into
	* a single string that can be stored in the database.
	*
	* @param array $alternate_cgis Array of alternate cgi-bin directories
	* @return string $alternative_cgibin_list Converted string that is
	* 	comma separated and contains the list of alternate cgi-bin
	*	directories that will be included in the scan
	*/
	public function make_alternate_cgibin_list($alternate_cgis) {
		$alternative_cgibin_list	= '';

		if (is_array($alternate_cgis)) {
			foreach($alternate_cgis as $key => $cgi) {
				if ($cgi == '') {
					continue;
				} else {
					$alternative_cgibin_list .= $cgi.':';
				}
			}

			$alternative_cgibin_list = substr($alternative_cgibin_list,0,-1);
		}

		return $alternative_cgibin_list;
	}

	/**
	* Create 'alternate email-to' string
	*
	* The list of alternate email recipients is created by using
	* elements on the settings page. The alternate emails however
	* are stored in a single text field in the database. This
	* function will convert the array of alternate emails into
	* a single string that can be stored in the database.
	*
	* @param array $alternate_emails Array of alternate email addresses
	*	to send the report to.
	* @return string $alternative_email_list Emails converted into a
	* 	single comma separated string that can be stored in the
	*	MySQL text field
	*/
	public function make_alternate_email_to_list($alternate_emails) {
		$alternative_email_list	= '';

		if (is_array($alternate_emails)) {
			foreach($alternate_emails as $key => $email) {
				if ($email == '') {
					continue;
				} else {
					$alternative_email_list .= $email.',';
				}
			}

			/**
			* The alternate email list will have a trailing comma
			* after the previous 'for' loop finishes. This removes
			* that trailing comma
			*/
			$alternative_email_list = substr($alternative_email_list,0,-1);
		}

		return $alternative_email_list;
	}
}

?>
