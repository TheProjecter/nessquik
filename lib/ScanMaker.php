<?php

require_once(_ABSPATH.'/lib/Devices.php');
require_once(_ABSPATH.'/lib/Clusters.php');

/**
* @author Tim Rupp
*/
class ScanMaker {
	/**
	* Array of all plugins that will be used in
	* the scan profile
	*
	* @var array
	*/
	public $scanner_set;

	/**
	* Profile ID of the profile that the returned
	* object will operate on.
	*
	* @var string
	*/
	private $profile_id;

	/**
	* Constructor
	*
	* Basic constructor that assigns values
	* to class variables.
	*
	* @param string $profile_id The profile ID for the
	*	scan profile that will be associated with
	*	the object that is created
	*/
	public function __construct($profile_id) {
		$this->scanner_set	= array();
		$this->profile_id 	= $profile_id;
	}

	/**
	* Get the machine list file's data
	*
	* The machine list will be populated by the list
	* of targets that Nessus needs to scan. Given a
	* list of machines, this function will generate
	* content that is suitable for placing in a targets
	* file so that Nessus understands it.
	*
	* Each machine is placed on it's own line.
	*
	* @param array $machine_list List of machines to format
	*	into a targets file
	* @return string Formatted output suitable for giving to Nessus
	*/
	public function get_ml_file_data($machine_list) {
		$output = '';

		foreach($machine_list as $key => $val) {
			$output .= $val."\n";
		}

		return $output;
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
	public function get_nrc_file_data($unique_list, $settings) {
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
		$output .= " Services[entry]:Number of connections done in parallel : = 10\n";
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

		// Begin nmap options
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
	* @param string $profile_id ID of the profile to get machines of
	* @return array Return array of machines listed in profile
	*/
	public function getMachines($profile_id) {
		require_once(_ABSPATH.'/lib/Devices.php');
		require_once(_ABSPATH.'/lib/Clusters.php');

		$db		= nessquikDB::getInstance();
		$_dev 		= Devices::getInstance();
		$_clu 		= Clusters::getInstance();

		$result	= array();
		$sql 	= array (
			'select' => "SELECT machine FROM profile_machine_list WHERE profile_id=':1';"
		);

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($profile_id);

		while($row = $stmt->fetch_assoc()) {
			$machine = $row['machine'];
			$type	= $_dev->determine_device_type($machine);

			/**
			* Clusters are special cases because they conflict with
			* hostnames by not having any special defining characters
			* in them. That's one of the reasons I do the cluster
			* processing here.
			*
			* Another is because in the settings for a specific scan
			* you can add and remove devices. Well, clusters are one
			* of those things you can remove and to distinctly know
			* which device is a cluster, I need to retain the :clu:
			* prefix on the cluster name.
			*/
			if ($type == "cluster") {
				$machine_list = array();

				foreach ($cluster as $key => $cluster_id) {
					$output = array();
					$output	= $_clu->get_cluster($cluster_id);

					foreach($output as $key2 => $val2) {
						// Index 1 is the hostname as pulled from miscomp
						$hostname = $val2[1];

						$tmp = array();
						$tmp = $_dev->get_mac_from_system($hostname);

						// The first index will hold the IP address
						array_push($machine_list, $tmp[0]);
					}
				}

				$result = array_merge($result,$machine_list);
			} else {
				$item	= $_dev->strip_device_type($machine);

				if (is_ip($item)) {
					$result[] = $item;
				} else if (is_cidr($item)) {
					$result[] = $item;
				} else if (is_vhost($item)) {
					$result[] = $item;
				} else {
					$item = gethostbyname($item);
					if ($item != '') {
						$result[] = $item;
					}
				}
			}
		}

		return $result;
	}

	/**
	* Merge all the plugins into the scanner set
	*
	* The scanner set by default is set to all plugins being
	* equal to 'no'. This function will iterate through the
	* entire scanner set and set all the plugins equal to 'yes'
	* if the setting in the scan profile specifies "all plugins"
	*
	* @see self::getProfilePlugins
	* @see self::scanner_set
	* @see self::profile_id
	*/
	public function merge_all() {
		$all = $this->getProfilePlugins($this->profile_id, 'all');

		if (count($all) > 0) {
			foreach($this->scanner_set as $key => $val) {
				$this->scanner_set[$key] = 'yes';
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
	* @return boolean False if none of the plugins in the profile
	*	are specifically a severity. True otherwise
	* @see self::getProfilePlugins
	* @see self::getPluginsBySeverity
	* @see self::profile_id
	* @see self::scanner_set
	*/
	public function merge_severities() {
		$severities = array();

		$severities = $this->getProfilePlugins($this->profile_id, 'sev');

		if (count($severities) < 1) {
			return false;
		}

		foreach($severities as $key => $sev) {
			$plugins = $this->getPluginsBySeverity($sev);

			foreach($plugins as $key2 => $plugin) {
				$this->scanner_set[$plugin] = 'yes';
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
	* @return boolean False if none of the plugins in the profile
	*	are specifically a family. True otherwise
	* @see self::getProfilePlugins
	* @see self::getPluginsByFamily
	* @see self::profile_id
	* @see self::scanner_set
	*/
	public function merge_families() {
		$families = array();

		$families = $this->getProfilePlugins($this->profile_id, 'fam');

		if (count($families) < 1) {
			return false;
		}

		foreach($families as $key => $fam) {
			$plugins = $this->getPluginsByFamily($fam);

			foreach($plugins as $key2 => $plugin) {
				$this->scanner_set[$plugin] = 'yes';
			}
		}

		return true;
	}

	/**
	* Merge plugins into the scanner set
	*
	* The scanner set by default is set to all plugins being
	* equal to 'no'. This function will iterate through the
	* entire scanner set and set the plugins equal to 'yes'
	* that have been specifically specified in the scan profile
	*
	* @return boolean False if none of the plugins in the profile
	*	are specifically a family. True otherwise
	* @see self::getProfilePlugins
	* @see self::profile_id
	* @see self::scanner_set
	*/
	public function merge_plugins() {
		$plugins = array();

		$plugins = $this->getProfilePlugins($this->profile_id, 'plu');

		if (count($plugins) < 1) {
			return false;
		}

		foreach($plugins as $key => $val) {
			$this->scanner_set[$plugin] = 'yes';
		}

		return true;
	}

	/**
	* Merge plugins in special plugin profile into the scanner set
	*
	* This function will iterate through plugins associated with
	* a special plugin profile and set the plugins equal to 'yes'
	* that have been specified in the special plugin profile
	*
	* @return boolean False if none of the plugins in the profile
	*	are specifically a family. True otherwise
	* @see self::getProfilePlugins
	* @see self::getSpecialProfileItems
	* @see self::merge_families
	* @see self::merge_severities
	* @see self::profile_id
	* @see self::scanner_set
	*/
	public function merge_plugin_profiles() {
		$special = $this->getProfilePlugins($this->profile_id, 'spe');

		if (count($special) < 1) {
			return false;
		}

		foreach($special as $key => $spe) {
			$profile_items = $this->getSpecialProfileItems($this->profile_id);

			foreach($profile_items as $key => $item) {
				$type 	= $item['plugin_type'];
				$plugin	= $item['plugin'];

				if ($type == 'fam') {
					$this->merge_families();
				} else if ($type == 'sev') {
					$this->merge_severities();
				} else if ($type == 'plu') {
					$this->scanner_set[$plugin] = 'yes';
				}
			}
		}

		return true;
	}

	/**
	* Get plugins associated with a profile
	*
	* This method returns a list of all the plugins
	* that will be used so the correct list in the
	* nessusrc file can be made
	*
	* @param string $profile_id Profile ID of scan
	*	to check to see if cancelled
	* @param string $pludin_type Type of plugin to
	*	select from the profile plugin list table
	* @return array List of plugins associated with
	* 	the given profile ID that are of the given type
	*/
	private function getProfilePlugins($profile_id, $plugin_type) {
		$db		= nessquikDB::getInstance();
		$results	= array();
		$sql 		= array(
			'select' => "SELECT plugin_type,plugin FROM profile_plugin_list WHERE profile_id=':1' AND plugin_type=':2';"
		);
		$stmt		= $db->prepare($sql['select']);

		$stmt->execute($profile_id, $plugin_type);

		while($row = $stmt->fetch_assoc()) {
			$results[] = $row['plugin'];
		}

		return $results;
	}

	/**
	* Get the special plugin profile items for a scan profile
	*
	* Scan profiles can have plugin profiles saved with them.
	* When parsing through the plugins list, these plugin
	* profiles will need to be broken apart and evaluated
	* down to their most basic parts (getting the plugins
	* for a family or severity that is part of the plugin
	* profile for example).
	*
	* This method will just return the top-most list of
	* items in a special plugin profile. It will not get
	* the final list of plugin IDs unless of course your
	* plugin profile consists entirely of individual plugins
	*
	* @param string $params Profile ID to get plugin profile items for
	* @return array Array of all the items is returned with
	*	the expectation that further sub-processing of the
	*	items may need to take place (Like breaking down
	*	severities into individual plugins).
	*/
	public function getSpecialProfileItems($profile_id) {
		$db		= nessquikDB::getInstance();
		$sql 		= array(
			'select' => "SELECT plugin_type,plugin FROM special_plugin_profile_items WHERE profile_id=':1'"
		);
		$stmt		= $db->prepare($sql);

		$stmt->execute($profile_id);

		while($row = $stmt->fetch_assoc()) {
			$result[] = array(
				'plugin_type'	=> $row['plugin_type'],
				'plugin'	=> $row['plugin']
			);
		}

		return $result;
	}

	/**
	* Wrapper to only pull plugins by severity
	*
	* This method just wraps around the getPluginsByType
	* method. It explicitly will pull only plugins for severities
	*
	* @param string $val Get plugins for this severity
	* @return array Array of plugins for the given severity
	* @see self::getPluginsByType
	*/
	public function getPluginsBySeverity($val) {
		$plugins = $this->getPluginsByType('sev', $val);

		return $plugins;
	}

	/**
	* Wrapper to only pull plugins by family
	*
	* This method just wraps around the getPluginsByType
	* method. It explicitly will pull only plugins for families
	*
	* @param string $params Get plugins for this family
	* @return array Array of plugins for the given family
	* @see self::getPluginsByType
	*/
	public function getPluginsByFamily($val) {
		$plugins = $this->getPluginsByType('fam', $val);

		return $plugins;
	}

	/**
	* Return plugin IDs for a given type
	*
	* This is a general method that is to be wrapped by other
	* methods that implement selecting plugins by the different
	* types.
	*
	* @param string $type Type of plugins to return. At this
	*	point in time, the valid values are 'sev' and 'fam'
	* 	for 'severity' and 'family' respectively
	* @param string $type_val Value to supply for the given type.
	*	For example it could be the name of a family, or the
	*	name of a secerity.
	* @return array List of plugin IDs associated with the given
	*	value for the given type
	* @see getPluginsBySeverity
	* @see getPluginsByFamily
	*/
	private function getPluginsByType($type, $type_val) {
		$db		= nessquikDB::getInstance();
		$plugins	= array();
		$sql		= array(
			'select' => "SELECT pluginid FROM plugins WHERE :1=':2' ORDER BY pluginid ASC;"
		);

		/**
		* Dunno how I missed this bug, apparently 'fam'
		* is not a field in the table. It's 'family'
		*/
		switch($type) {
			case "fam":
				$type = "family";
				break;
		}

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($type, $type_val);

		while($row = $stmt->fetch_assoc()) {
			$plugins[] = $row['pluginid'];
		}

		return $plugins;
	}

	/**
	* Retrieve settings for a profile
	*
	* Return the settings in the profile_settings table that
	* are needed during the scan creation. See the SQL
	* statement in this method for a list of the fields
	* that are returned.
	*
	* @param string $profile_id Profile ID of scan to pull settings for
	* @return array Array of profile settings
	*/
	public function getProfileSettings($profile_id) {
		$db		= nessquikDB::getInstance();
		$sql = "SELECT 	report_format,
				ping_host_first,
				alternative_cgibin_list,
				save_scan_report,
				alternative_email_list,
				custom_email_subject,
				port_range
			FROM profile_settings 
			WHERE profile_id=':1'
			LIMIT 1;";

		$stmt = $db->prepare($sql);
		$stmt->execute($profile_id);

		return $stmt->fetch_assoc();
	}

	/**
	* Get a list of all plugins
	*
	* This function will query the plugins database and return
	* an array that is indexed by plugin ID. The value of each
	* entry in the array will be 'no'. The array that is returned
	* can be looped through right away to create the plugin list
	* for a nessusrc file.
	*
	* @return array Array of plugin IDs with the value of each entry set to 'no'
	*/
	public function getAllPlugins() {
		$db	= nessquikDB::getInstance();
		$set	= array();
		$sql 	= array(
			'select' => "SELECT pluginid FROM plugins ORDER BY pluginid ASC;"
		);

		$stmt = $db->prepare($sql['select']);
		$stmt->execute();

		while($row = $stmt->fetch_assoc()) {
		        $set[$row['pluginid']] = 'no';
		}

		return $set;
	}
}

?>
