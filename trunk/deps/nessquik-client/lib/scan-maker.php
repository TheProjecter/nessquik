<?php

require_once(_ABSPATH.'/lib/Devices.php');

/**
* Make the list of machines
*
* From the command line Nessus will read in a list of machines
* to scan. This will create the temporary file that includes
* all the machines that will be scanned.
*
* @param array $machine_list List of machines to add to the
*	machine list. This can include CIDR blocks because Nessus
*	understands those
* @return string Name of machine list file that was created
*/
function make_ml_file($data) {
	$rand1		= mt_rand(0,1000000);
	$rand2		= mt_rand(0,1000000);
	$filename	= _NESSUS_STORE . '/' . $rand1 . '_' . $rand2 . '.ml';

	$fh = fopen($filename, "w");

	if (!$fh) {
		syslog(LOG_ERR, "Couldn't create the machine list file !");
		exit;
	}

	// do an exclusive lock
	if (flock($fh, LOCK_EX)) {
		fwrite($fh, $data);
		flock($fh, LOCK_UN); // release the lock
	} else {
		syslog(LOG_WARNING, "Couldn't lock the machine list file !");
		exit;
	}

	fclose($fh);

	chmod($filename, 0644);
	return $filename;
}

/**
* Get the data for a machine list file
*
* This function will parse a list of machines and
* return a string that is formatted in a way that
* Nessus will understand it. That data can then
* be written directly to a file for usage by nessus
*
* @param array $machine_list List of machines to format
*	in a way that can be written to a file that
*	Nessus will understand
* @return string Formatted list of machines for inclusion
*	in a file that Nessus will read when determining
*	target machines
*/
function get_ml_file_data($machine_list) {
	$output = '';

	foreach($machine_list as $key => $val) {
		$output .= $val."\n";
	}

	return $output;
}

/**
* Create temporary nessusrc file
*
* Nessus accepts a nessusrc file parameter on the command line.
* This function will create the appropriate nessusrc file for
* the scan that is to be run, so that when Nessus is called
* from the command line, it has the appropriate settings written
* to a nessusrc file for the scan that is going to be run.
*
* @param array $unique_list Unique array of plugin IDs
* @param array $settings Array of user settings for the specific scan
* @return string Name of nessusrc file that was created
*/
function make_nrc_file($data) {
	$rand1		= mt_rand(0,1000000);
	$rand2		= mt_rand(0,1000000);
	$filename	= _NESSUS_STORE . '/' . $rand1 . '_' . $rand2 . '.nrc';
	$fh 		= fopen($filename, "w");

	if (!$fh) {
		syslog(LOG_ERR, "Couldn't create the nesssurc file !");
		exit;
	}

	// do an exclusive lock
	if (flock($fh, LOCK_EX)) {
		fwrite($fh, $data);
		flock($fh, LOCK_UN); // release the lock
	} else {
		syslog(LOG_WARNING, "Couldn't lock the nessusrc file !");
		exit;
	}

	fclose($fh);

	chmod($filename, 0644);
	return $filename;
}

/**
* Create nessusrc file data
*
* Nessus' nessusrc files are used by the Nessus client
* to tell the server how to handle the scan that the
* client wants to schedule. Scan parameters are usually
* included in the rc files as well as usernames, the
* list of plugins, etc. This function will return the
* full contents of a proper nessusrc file so that
* the data can be written to a file on disk for use
* by the Nessus client.
*
* @param array $unique_list Unique list of plugins to
*	include in the vulnerability scan
* @param array $settings Array of scan settings that
*	nessquik supports that will be used to tweak
*	the operation of the Nessus server
* @return string Full nessusrc file data that can be
* 	written to a file on disk for use by the Nessus
*	client
*/
function get_nrc_file_data($unique_list, $settings) {
	// Write a common header
	$output = "# Nessus Client Preferences File\n";

	// In case they defined a trusted CA
	if (_TRUSTED_CA != '') {
		$output .= "trusted_ca = "._TRUSTED_CA."\n";
	}

	/**
	* This defines the start of the scanner list
	*
	* Comments next to each plugin were taken from the nessus
	* mailing list, or added by me from my own poking
	*
	* http://mail.nessus.org/pipermail/nessus/2003-February/msg00084.html
	*/
	$output .= "begin(SCANNER_SET)\n";

	if ($settings['ping_host_first']) {
		$output .= " 10180 = yes\n";	// Ping remote host
	} else {
		$output .= " 10180 = no\n";
	}

	$output .= " 10278 = no\n";
	$output .= " 10331 = no\n";		// FTP bounce scan
	$output .= " 10335 = yes\n";		// TCP connect() scan
	$output .= " 10841 = no\n";		// 
	$output .= " 10336 = no\n";		// NMAP
	$output .= " 10796 = no\n";		// LaBrea
	$output .= " 11219 = no\n";		// TCP SYN scan
	$output .= " 14259 = no\n";
	$output .= " 14272 = no\n";		// Netstat scanner
	$output .= " 14274 = no\n";		// SNMP scanner
	$output .= " 14663 = no\n";
	$output .= " 11840 = no\n";		// Exclude toplevel domain wildcard host
	$output .= "end(SCANNER_SET)\n";

	// This defines the start of the plugin list
	$output .= "begin(PLUGIN_SET)\n";

	foreach($unique_list as $key => $val) {
		$output .= $key." = ".$val."\n";
	}

	$output .= "end(PLUGIN_SET)\n\n";

	// Define the server preferences
	$output .= "begin(SERVER_PREFS)\n";
	$output .= " max_hosts = 20\n";
	$output .= " max_checks = 50\n";
	#fwrite($fh, " log_whole_attack = no\n");
	$output .= " optimize_test = yes\n";
	$output .= " language = english\n";
	$output .= " checks_read_timeout = 5\n";
	$output .= " non_simult_ports = 139, 445\n";
	$output .= " plugins_timeout = 120\n";
	$output .= " safe_checks = yes\n";
	$output .= " auto_enable_dependencies = yes\n";
	$output .= " silent_dependencies = no\n";
	$output .= " use_mac_addr = no\n";
	$output .= " save_knowledge_base = no\n";
	$output .= " kb_restore = no\n";
	$output .= " slice_network_addresses = no\n";
	$output .= " host_expansion = ip\n";
	$output .= " reverse_lookup = no\n";
	$output .= " unscanned_closed = no\n";

	if ($settings['alternative_cgibin_list'] != '') {
		$output .= " cgi_path = ".$settings['alternative_cgibin_list']."\n";
	}

	if ($settings['ping_host_first']) {
		$output .= " ping_hosts = yes\n";
	} else {
		$output .= " ping_hosts = no\n";
	}

	$output .= " port_range = ".$settings['port_range']."\n";
	$output .= "end(SERVER_PREFS)\n";

	// Define the plugin preferences
	$output .= "begin(PLUGINS_PREFS)\n";

	$output .= " Global variable settings[checkbox]:Enable CGI scanning = yes\n";
	$output .= " Global variable settings[checkbox]:Enable experimental scripts = no\n";
	$output .= " Global variable settings[entry]:HTTP User-Agent = Mozilla/4.75 [en] (X11, U; Nessus)\n";
	$output .= " Global variable settings[checkbox]:Thorough tests (slow) = yes\n";
	$output .= " Services[entry]:Number of connections done in parallel : = 6\n";
	$output .= " Services[entry]:Network connection timeout : = 5\n";
	$output .= " Services[entry]:Network read/write timeout : = 5\n";
	$output .= " Services[entry]:Wrapped service read timeout : = 2\n";

	if (!$settings['ping_host_first']) {
		$output .= " Ping the remote host[entry]:TCP ping destination port(s) : = built-in\n";
		$output .= " Ping the remote host[checkbox]:Do a TCP ping = no\n";
		$output .= " Ping the remote host[checkbox]:Do an ICMP ping = no\n";
		$output .= " Ping the remote host[entry]:Number of retries (ICMP) : = 6\n";
		$output .= " Ping the remote host[checkbox]:Make the dead hosts appear in the report = no\n";
		$output .= " Ping the remote host[checkbox]:Do an applicative UDP ping (DNS,RPC...) = no\n";
		$output .= " Ping the remote host[checkbox]:Log live hosts in the report = no\n";
	} else {
		$output .= " Ping the remote host[entry]:TCP ping destination port(s) : = built-in\n";
		$output .= " Ping the remote host[checkbox]:Do a TCP ping = yes\n";
		$output .= " Ping the remote host[checkbox]:Do an ICMP ping = yes\n";
		$output .= " Ping the remote host[entry]:Number of retries (ICMP) : = 6\n";
		$output .= " Ping the remote host[checkbox]:Make the dead hosts appear in the report = no\n";
		$output .= " Ping the remote host[checkbox]:Do an applicative UDP ping (DNS,RPC...) = yes\n";
		$output .= " Ping the remote host[checkbox]:Log live hosts in the report = no\n";
	}

	// Begin Nmap options
	$output .= " Nmap[radio]:TCP scanning technique : = SYN scan\n";
	$output .= " Nmap[checkbox]:UDP port scan = no\n";
	$output .= " Nmap[checkbox]:RPC port scan = no\n";

	if ($settings['ping_host_first']) {
		$output .= " Nmap[checkbox]:Ping the remote host = yes\n";
	} else {
		$output .= " Nmap[checkbox]:Ping the remote host = no\n";
	}

	$output .= " Nmap[checkbox]:Identify the remote OS = no\n";
	$output .= " Nmap[checkbox]:Use hidden option to identify the remote OS = no\n";
	$output .= " Nmap[checkbox]:Fragment IP packets (bypasses firewalls) = no\n";
	$output .= " Nmap[checkbox]:Get Identd info = no\n";
	$output .= " Nmap[radio]:Port range = Fast scan\n";
	$output .= " Nmap[checkbox]:Do not randomize the  order  in  which ports are scanned = yes\n";
	$output .= " Nmap[entry]:Source port : = any\n";
	$output .= " Nmap[radio]:Timing policy : = Normal\n";

	$output .= " Nmap (NASL wrapper)[radio]:TCP scanning technique : = SYN scan\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:UDP port scan = no\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:Service scan = no\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:RPC port scan = no\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:Identify the remote OS = no\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:Use hidden option to identify the remote OS = no\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:Fragment IP packets (bypasses firewalls) = no\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:Get Identd info = no\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:Do not randomize the  order  in  which ports are scanned = no\n";
	$output .= " Nmap (NASL wrapper)[radio]:Timing policy : = Auto (nessus specific!)\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:Do not scan targets not in the file = no\n";
	$output .= " Nmap (NASL wrapper)[checkbox]:Run dangerous port scans even if safe checks are set = no\n";

	$output .= " Nmap (NASL wrapper)[entry]:Source port : =\n";
	$output .= " Nmap (NASL wrapper)[entry]:Host Timeout (ms) : =\n";
	$output .= " Nmap (NASL wrapper)[entry]:Min RTT Timeout (ms) : =\n";
	$output .= " Nmap (NASL wrapper)[entry]:Max RTT Timeout (ms) : =\n";
	$output .= " Nmap (NASL wrapper)[entry]:Initial RTT timeout (ms) : =\n";
	$output .= " Nmap (NASL wrapper)[entry]:Ports scanned in parallel (max) =\n";
	$output .= " Nmap (NASL wrapper)[entry]:Ports scanned in parallel (min) =\n";
	$output .= " Nmap (NASL wrapper)[entry]:Minimum wait between probes (ms) =\n";
	$output .= " Nmap (NASL wrapper)[file]:File containing grepable results : =\n";

	$output .= "end(PLUGINS_PREFS)\n";

	return $output;
}

/**
* Create list of machines to scan
*
* This function will query the database for the list
* of all the machines that were specified when the
* scan was created
*
* @param string $profile_id Profile ID of the scan to get machine list of
* @return array Return array of machines listed in profile
*/
function make_machine_list($profile_id) {
	global $client;

	$client->query('jobs.getMachines', _CLIENT_KEY, $profile_id);
	$machines = $client->getResponse();

	return $machines;
}

/**
* Merge all the plugins into the scanner set
*
* The scanner set by default is set to all plugins being
* equal to 'no'. This function will iterate through the
* entire scanner set and set all the plugins equal to 'yes'
* if the setting in the scan profile specifies "all plugins"
*
* @param string $profile_id Profile ID of the scan whose
*	plugin list is to be updated 
* @param array $scanner_set List of all plugins used by nessus.
*	The array is indexed by plugin ID with the values of
*	each item being either 'yes' if the plugin should be
*	enabled, or 'no' if it should not be enabled
*/
function merge_all($profile_id, &$scanner_set) {
	global $client;

	$client->query('jobs.getProfilePlugins', _CLIENT_KEY, $profile_id, 'all');
	$all = $client->getResponse();

	if (count($all) > 0) {
		foreach($scanner_set as $key => $val) {
			$scanner_set[$key] = 'yes';
		}
	}
}

/**
* Merge plugins, based on severity, into the scanner set
*
* The scanner set by default is set to all plugins being
* equal to 'no'. This function will iterate through the
* entire scanner set and set all the plugins equal to 'yes'
* if the scan profile specifies a particular severity to
* use.
*
* @param string $profile_id Profile ID of the scan whose
*	plugin list is to be updated 
* @param array $scanner_set List of all plugins used by nessus.
*	The array is indexed by plugin ID with the values of
*	each item being either 'yes' if the plugin should be
*	enabled, or 'no' if it should not be enabled
*/
function merge_severities($profile_id, &$scanner_set) {
	global $client;
	$severities = array();

	$client->query('jobs.getProfilePlugins', _CLIENT_KEY, $profile_id, 'sev');
	$severities = $client->getResponse();

	if (count($severities) < 1)
		return;

	foreach($severities as $key => $sev) {
		$client->query('jobs.getPluginsBySeverity', $sev);
		$plugins = $client->getResponse();

		foreach($plugins as $key2 => $plugin) {
			$scanner_set[$plugin] = 'yes';
		}
	}
}

/**
* Merge plugins, based on family, into the scanner set
*
* The scanner set by default is set to all plugins being
* equal to 'no'. This function will iterate through the
* entire scanner set and set all the plugins equal to 'yes'
* if the scan profile specifies a particular family to
* use.
*
* @param string $profile_id Profile ID of the scan whose
*	plugin list is to be updated 
* @param array $scanner_set List of all plugins used by nessus.
*	The array is indexed by plugin ID with the values of
*	each item being either 'yes' if the plugin should be
*/
function merge_families($profile_id, &$scanner_set) {
	global $client;

	$families = array();

	$client->query('jobs.getProfilePlugins', _CLIENT_KEY, $profile_id, 'fam');
	$families = $client->getResponse();

	if (count($families) < 1) {
		return;
	}

	foreach($families as $key => $fam) {
		$client->query('jobs.getPluginsByFamily', $fam);
		$plugins = $client->getResponse();

		foreach($plugins as $key2 => $plugin) {
			$scanner_set[$plugin] = 'yes';
		}
	}
}

/**
* Merge plugins into the scanner set
*
* The scanner set by default is set to all plugins being
* equal to 'no'. This function will iterate through the
* entire scanner set and set the plugins equal to 'yes'
* that have been specifically specified in the scan profile
*
* @param string $profile_id Profile ID of the scan whose
*	plugin list is to be updated 
* @param array $scanner_set List of all plugins used by nessus.
*	The array is indexed by plugin ID with the values of
*	each item being either 'yes' if the plugin should be
*/
function merge_plugins($profile_id, &$scanner_set) {
	global $client;

	$plugins = array();

	$client->query('jobs.getProfilePlugins', _CLIENT_KEY, $profile_id, 'plu');
	$plugins = $client->getResponse();

	if (count($plugins) < 1)
		return;

	foreach($plugins as $key => $val) {
		$scanner_set[$plugin] = 'yes';
	}
}

/**
* Merge plugins in special plugin profile into the scanner set
*
* This function will iterate through plugins associated with
* a special plugin profile and set the plugins equal to 'yes'
* that have been specified in the special plugin profile
*
* @param string $profile_id Profile ID of the scan whose
*	plugin list is to be updated 
* @param array $scanner_set List of all plugins used by nessus.
*	The array is indexed by plugin ID with the values of
*	each item being either 'yes' if the plugin should be
*/
function merge_plugin_profiles($profile_id, &$scanner_set) {
	global $client;

	$client->query('jobs.getProfilePlugins', _CLIENT_KEY, $profile_id, 'spe');
	$special = $client->getResponse();

	if (count($special) < 1) {
		return;
	}

	foreach($special as $key => $spe) {
		$client->query('jobs.getSpecialProfileItems', $profile_id);
		$profile_items = $client->getResponse();

		foreach($profile_items as $key => $item) {
			$type 	= $item['plugin_type'];
			$plugin	= $item['plugin'];

			if ($type == 'fam') {
				merge_families($profile_id, $scanner_set);
			} else if ($type == 'sev') {
				merge_severities($profile_id, $scanner_set);
			} else if ($type == 'plu') {
				$scanner_set[$plugin] = 'yes';
			}
		}
	}
}

?>
