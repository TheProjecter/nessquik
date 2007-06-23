<?php

session_name('nessquik');
session_start();

set_time_limit(0); 

// Used for including files
if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(__FILE__)));
}

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/lib/Smarty.php');
require_once(_ABSPATH.'/lib/functions.php');
require_once(_ABSPATH.'/lib/settings.php');
require_once(_ABSPATH.'/lib/Netmask.php');
require_once(_ABSPATH.'/lib/process.php');
require_once(_ABSPATH.'/lib/User.php');
require_once(_ABSPATH.'/lib/Devices.php');
require_once(_ABSPATH.'/lib/Clusters.php');
require_once(_ABSPATH.'/db/nessquikDB.php');

$db 	= nessquikDB::getInstance();
$tpl	= SmartyTemplate::getInstance();
$_clu	= Clusters::getInstance();
$_usr	= User::getInstance();
$_dev	= Devices::getInstance();
$items	= array();

$proper 		= $_usr->get_proper_name();

$tpl->template_dir      = _ABSPATH.'/templates/';
$tpl->compile_dir	= _ABSPATH.'/templates_c/';

$tpl->assign('proper', $proper);
$tpl->display('processing.tpl');
@ob_end_flush();
flush();

$items = import_var('item', 'P');

if (is_null($items)) {
	$items = array();
}

$sql = array(
	'add_machine_list' => "	INSERT INTO profile_machine_list (
					`profile_id`,
					`machine`) 
				VALUES (':1',':2');",

	'add_plugin_list' => "	INSERT INTO profile_plugin_list (
					`profile_id`,
					`plugin_type`,
					`plugin`) 
				VALUES (':1',':2',':3');",

	'add_profile' => "	INSERT INTO profile_list(
					`profile_id`,
					`username`,
					`date_scheduled`,
					`date_finished`,
					`status`) 
				VALUES (':1',':2',':3',NULL,':4');",

	'save_scan' => "INSERT INTO profile_settings (
				`username`,
				`profile_id`,
				`setting_name`,
				`setting_type`) 
			VALUES (':1',':2',':3',':4');",

	'insert_progress' => "  INSERT INTO scan_progress (
					`profile_id`,
					`portscan_percent`,
					`attack_percent`)
				VALUES (':1','0','0');",

	'update' => "UPDATE profile_settings SET ",

	'recurrence' => "INSERT INTO recurrence (
				`profile_id`,
				`recur_type`,
				`the_interval`,
				`specific_time`,
				`rules_string`) 
			VALUES (':1', ':2', ':3', ':4', ':5') 
			ON DUPLICATE KEY UPDATE 
				recur_type=':6', 
				the_interval=':7', 
				specific_time=':8', 
				rules_string=':9';",

	'update_time' => "UPDATE profile_list 
			SET date_scheduled=':1' 
			WHERE profile_id=':2';",

	'update_status' => "UPDATE profile_list 
			SET status=':1' 
			WHERE profile_id=':2'",

	'select_profile' => "SELECT profile_id,setting_name FROM profile_settings WHERE setting_id=':1' AND username=':2';",

	'reschedule_scan' => "UPDATE profile_list SET date_scheduled=':1', status='P' WHERE profile_id=':2';"
);

$scan_type 		= import_var('scan_type', 'P');
$list			= '';
$printable_accepted	= array();
$saved_scans		= array();
$output			= '';

/**
* I wanted to make a pseudo random string, so I take the unix
* timestamp and pull a random number between 0 and a million
* which I then add to the timestamp and then md5 hash the thing
*/
$profile_id		= md5(rand(0,1000000) + time());
$time 			= strftime("%Y-%m-%d %T", time());
$plugins 		= array();

/**
* machine_list is an array of all possible entities to scan.
* It is used only for the 'list' entry type and it will contain
* only items that have not been 'verified' yet.
*
* Verified means that the user is allowed to scan the particular
* machine. Once an item has been verified, it will be moved from
* the machine_list array and added to the ok_computers array
*/
$machine_list		= array();

/**
* Raw list of devices specified by the user that need to be scanned
*/
$devices		= array();

/**
* Contains the final list of machines that are going to be saved
* to the database for later scanning
*/
$ok_computers		= array();

/**
* An array of the settings that were passed to the process script.
* The entries in this array are processed and ready for insertion
* into the profile_settings table
*/
$settings		= array();

$username 		= import_var('username', 'S');
$scan_name		= import_var('scan_name', 'P', 'scan_name');
$user_id		= $_usr->get_id($username);

/**
* Devices is any and all pre-verified target. This includes
* registered machines, clusters, whitelist entries and vhosts
*/
$devices 		= import_var('dev', 'P');

// To prevent errors later, set the array to empty if no devices were specified
if (!is_array($devices))
	$devices = array();

// List is the textarea that users can enter individual items into
$list		= str_replace(' ','',import_var('list', 'P', 'list'));

// If nothing was sent, then raise an error and quit
if ((count($devices) < 1) && ($list == '')) {
	echo "<script type='text/javascript'>"
	. "document.getElementById('processing_steps').innerHTML = \"<span style='color: #000;'>"
	. "- You need to enter a computer or list of computers to scan<span><p><a href='../index.php'>Create a new Scan</a>\""
	. "</script>";
	hide_spinner();
	@ob_end_flush();
	flush();
	exit;
}

/**
* If pre-confirmed devices were specifed, get the list of devices
* as they are going to be printed to the screen, so I can hold on
* to them until that time
*/
if(count($devices) > 0) {
	// Assign the list of accepted machines that will be printed to the screen
	$printable_accepted = $_dev->get_devices_by_type($devices,'all');
}

// Need to have an ID to schedule a scan
if($user_id == 0) {
	$tpl->assign('MESSAGE', "Your username could not be matched with an ID");
	$tpl->assign('RETURN_LINK', "<a href='../index.php'>Return to create a scan</a>");
	$tpl->display('actions_done.tpl');
	exit;
}

/**
* If the user has chosen a method that hasnt already been verfied,
* then I need to get a list of machines that they are allowed to scan
*/
if ($list != '') {
	$whitelist	= $_usr->get_whitelist($username);
}

/**
* If the user chose to re-run a saved scan, then process that independently
* and only run those saved scans. In this case, saved scans will be scheduled
* and the script will exit.
*
* The user can only choose either a saved scan or select devices. You cannot
* schedule a saved scan and, at the same time, create a new scan.
*/
if($_dev->count_devices_by_type($devices,'saved') > 0) {
	$saved_scans = $_dev->get_devices_by_type($devices, 'saved');
	$count = 0;
	$saved_scan_names = array();

	$stmt1 = $db->prepare($sql['select_profile']);
	$stmt2 = $db->prepare($sql['reschedule_scan']);

	foreach($saved_scans as $key => $setting_id) {
		$stmt1->execute($setting_id, $username);

		$row 			= $stmt1->fetch_assoc();
		$profile_id		= $row['profile_id'];
		$saved_scan_names[]	= $row['setting_name'];

		$stmt2->execute($time, $profile_id);
	}

	$output .= "<h5>Rescheduling the selected saved scans</h5>";

	foreach($saved_scan_names as $key => $val) {
		if ($count == 6) {
			$output .= "</tr><tr>";
			$count = 0;
		}
		$output .= "<td style='width: 25%;'>- Saved Scan: $val</td>";
		$count += 1;
	}

	hide_spinner();
	echo "<script type='text/javascript'>"
	. "tmpd = document.getElementById('processing_steps').innerHTML;\n"
	. "document.getElementById('processing_steps').innerHTML = tmpd +"
	. "\"$output<p>\""
	. "</script>";

	@ob_end_flush();
	flush();
}

/**
* If the user has specified a registered machine in their list
* then just extract the registered machines and add them to the ok
* list
*/
if($_dev->count_devices_by_type($devices,'registered') > 0) {
	// The false argument tells the function to not remove the
	// prefix from the device (:reg:,:clu:,etc)
	$registered 	= $_dev->get_devices_by_type($devices,'registered',false);

	$ok_computers	= array_merge($ok_computers,$registered);
}

if($_dev->count_devices_by_type($devices,'cluster') > 0) {
	$clusters	= $_dev->get_devices_by_type($devices,'cluster',false);
	$ok_computers	= array_merge($ok_computers,$clusters);
}

if ($_dev->count_devices_by_type($devices,'whitelist') > 0) {
	$whitelists	= $_dev->get_devices_by_type($devices,'whitelist',false);
	$ok_computers	= array_merge($ok_computers,$whitelists);
}

if ((count($saved_scans) > 0) && (count($ok_computers) < 1)) {
	echo "<script type='text/javascript'>"
	. "tmpd = document.getElementById('processing_steps').innerHTML;\n"
	. "document.getElementById('processing_steps').innerHTML = tmpd + \"<h5>Scan Scheduled</h5>You will receive an email with your scan results soon."
	. "<p><a href='../index.php'>Schedule another scan</a>\""
	. "</script>";
	@ob_end_flush();
	flush();
	exit;
}

if ($list) {
	$data 		= list_entries_to_array($list);
	$machine_list 	= list_items_to_machine_list($data);

	if (_RELEASE == "fermi") {
		$machine_list	= skim_whitelist_to_verify_nodes($machine_list, $whitelist, $ok_computers);

		/**
		* So far I've just skimed the surface of the
		* whitelist. If listed items still remain, then a bit deeper
		* inspection of the whitelist might be neccesary.
		*
		* This deeper inspection will undeniably prove whether a
		* specified item is whitelisted or not
		*/
		if ( (count($machine_list) > 0) && (count($whitelist) > 0) ) {
			$wl = array();

			/**
			* This breaks down whitelist items more completely
			* so that in the next couple of lines I can fully
			* determine if an entry is or is not in the whitelist
			*/
			$wl = $_usr->prep_whitelist($whitelist);

			// Run deeper validation routine
			whitelist_dig_deep_verify_nodes($wl, $machine_list, $ok_computers);
		}

		// strip out the empty index entries
		$machine_list = array_filter($machine_list, "strip_empty");

		/**
		* If the machine_list still has entries in it by the time I get to this
		* point, then those entries need to be looked up in MISCOMP. 
		*/
		if (count($machine_list) > 0) {
			echo "<script type='text/javascript'>"
			. "tmpd = document.getElementById('processing_steps').innerHTML;\n"
			. "document.getElementById('processing_steps').innerHTML = tmpd +"
			. "\"<span style='color: #000;'>- Several items were not found in your whitelist. "
			. "Checking to see if they are in SysadminDB or Miscomp</span>\""
			. "</script>";
		
			@ob_end_flush();
			flush();

			$accepted_list	= $_usr->get_accepted_machine_list($user_id);

			// Run through the whitelist one more time using the hostnames
			// or ips to see if they are in the whitelist
			check_registered_host_or_ip_in_list($accepted_list, $ok_computers, $machine_list);
		}

		if (count($ok_computers) < 1) {
			echo "<script type='text/javascript'>"
			. "document.getElementById('processing_steps').innerHTML = \"<span style='color: #000;'>- You "
			. "do not have permission to scan the machines you chose<span><p>"
			. "<a href='../index.php'>Create a new Scan</a>\""
			. "</script>";
			hide_spinner();
			@ob_end_flush();
			flush();
			exit;
		}
	} else {
		foreach($machine_list as $key => $val) {
			$ok_computers[] = ":gen:$val";
		}
		$machine_list = array();
	}
}

// Filter out empty elements
$machine_list = array_filter($machine_list, "strip_empty");
$ok_computers = array_filter($ok_computers, "strip_empty");

// Remove any duplicates (may be possible);
$machine_list = array_unique($machine_list);
$ok_computers = array_unique($ok_computers);

// If the user hasnt been granted any machines to scan by this point, then
// it's pointless to continue further. Spit and error and exit
if (count($ok_computers) < 1) {
	echo "<script type='text/javascript'>"
	. "document.getElementById('processing_steps').innerHTML = \"<span style='color: #000;'>- You "
	. "do not have permission to scan the machines you chose<span><p>"
	. "<a href='../index.php'>Create a new Scan</a>\""
	. "</script>";
	hide_spinner();
	@ob_end_flush();
	flush();
	exit;
}

$output .= "<h5>Going to scan the following</h5><table width='100%'><tr>";
$count = 0;

/**
* Print out an accepted machines list that resembles the inputs
* that the user specified instead of printing out like a whole
* bunch of individual addresses.
*/
if (count($ok_computers) < 1) {
	$output .= "<td>None</td>";
} else {
	foreach($ok_computers as $key => $val) {
		$type 	= $_dev->determine_device_type($val);
		$val	= $_dev->strip_device_type($val);

		/**
		* I store the cluster ID in the database, so for readability
		* purposes, convert the cluster ID back into a cluster name
		* just so the user can see that yes in fact they scheduled a
		* scan against a cluster
		*/
		if ($type == "cluster") {
			$val	= $_clu->get_cluster_name_by_id($val);
		}

		$type 	= ucfirst($type);

		if ($count == 4) {
			$output .= "</tr><tr>";
			$count = 0;
		}
		$output .= "<td style='width: 25%;'>- $type: $val</td>";
		$count += 1;
	}
}
$output .= "</tr></table>";

/**
* Print out the list of rejected items here.
*/
if (count($machine_list) > 0) {
	$output .= "<h5>You dont have permission to scan the following</h5><table width='100%'><tr>";
	$count = 0;

	foreach($machine_list as $key => $val) {
		$type 	= ucfirst($_dev->determine_device_type($val));

		if ($count == 4) {
			$output .= "</tr><tr>";
			$count = 0;
		}
		$output .= "<td style='width: 25%;'>- $val</td>";
		$count += 1;
	}
	$output .= "</tr></table>";
}


// Send everything back to the browser so the user can observe progress
hide_spinner();
echo "<script type='text/javascript'>"
. "document.getElementById('processing_steps').innerHTML = \"$output<p/>\";"
. "</script>";

@ob_end_flush();
flush();

// Here's all the SQL that could possibly be run
$stmt1 = $db->prepare($sql['add_machine_list']);
$stmt2 = $db->prepare($sql['add_plugin_list']);
$stmt3 = $db->prepare($sql['add_profile']);
$stmt5 = $db->prepare($sql['save_scan']);
$stmt6 = $db->prepare($sql['insert_progress']);
$stmt7 = $db->prepare($sql['recurrence']);
$stmt8 = $db->prepare($sql['update_time']);
$stmt10 = $db->prepare($sql['update_status']);

/**
* Keep placeholder for stmt9 because it is used
* at the end of the settings update
*
*	$stmt9 = $db->prepare($sql['update']);
*/

// The following populates the plugins table
if (in_array("a:all",$items)) {
	$stmt2->execute($profile_id, 'all', 'all');
} else {
	foreach ($items as $key => $val) {
		$item	= '';
		$tmp 	= explode(':', $val);

		$prefix = $tmp[0];

		// Some plugin families can have colons in them
		if (count($tmp) > 2) {
			for ($x = 1; $x < count($tmp); $x++) {
				$item	.= $tmp[$x] . ':';
			}

			$item 	= trim(substr($item,0,-1));
		} else {
			$item	= trim($tmp[1]);
		}

		// Severity items are prefixed with an 's:'
		if ($prefix == 's') {
			// Remove the prefix before inserting into table
			$stmt2->execute($profile_id, 'sev', $item);

		// Family items are prefixed with an 'f:'
		} else if ($prefix == 'f') {
			// Remove the prefix before inserting into table
			$stmt2->execute($profile_id, 'fam', $item);

		// Regular plugins are prefixed with a 'p:'
		} else if ($prefix == 'p') {
			// Remove the prefix before inserting into table
			$stmt2->execute($profile_id, 'plu', $item);

		// Special plugin profiles are prefixed with a 'sp:'
		} else if ($prefix == 'sp') {
			$stmt2->execute($profile_id, 'spe', $item);
		}
	}
}

/**
* Add the general profile information to the profiles list
*
* Note that I'm setting the status to "not ready" because
* I still need to update the settings below. Once the settings
* are updated, the status will be set to pending. This
* prevents any possibility of the scan runner coming along
* and scheduling the scan while I'm still processing it.
*/
if(($scan_name != '') && ($scan_name != 'new scan') && !is_numeric($scan_name)) {
	$stmt3->execute($profile_id,$username,$time,'N');

	$stmt5->execute($username,$profile_id,$scan_name,'user');
} else {
	$stmt3->execute($profile_id,$username,$time,'N');

	// A simple if-else statement to change wording around based on number of machines
	if (count($ok_computers) > 2) {
		$type_1		= $_dev->determine_device_type($ok_computers[0]);
		$type_2		= $_dev->determine_device_type($ok_computers[1]);

		$computer_one 	= $_dev->strip_device_type($ok_computers[0]);
		$computer_two	= $_dev->strip_device_type($ok_computers[1]);

		if ($type_1 == "cluster") {
			$computer_one = $_clu->get_cluster_name_by_id($computer_one);
		}

		if ($type_2 == "cluster") {
			$computer_two = $_clu->get_cluster_name_by_id($computer_two);
		}

		$the_scan_name 	= "Scan of $computer_one, $computer_two...";

		$stmt5->execute($username,$profile_id,$the_scan_name,'user');
	} elseif (count($ok_computers) > 1) {
		$type_1		= $_dev->determine_device_type($ok_computers[0]);
		$type_2		= $_dev->determine_device_type($ok_computers[1]);

		$computer_one	= $_dev->strip_device_type($ok_computers[0]);
		$computer_two	= $_dev->strip_device_type($ok_computers[1]);

		if ($type_1 == "cluster") {
			$computer_one = $_clu->get_cluster_name_by_id($computer_one);
		}

		if ($type_2 == "cluster") {
			$computer_two = $_clu->get_cluster_name_by_id($computer_two);
		}

		$the_scan_name 	= "Scan of $computer_one and $computer_two";

		$stmt5->execute($username,$profile_id,$the_scan_name,'user');
	} else {
		$type_1		= $_dev->determine_device_type($ok_computers[0]);

		$computer_one 	= $_dev->strip_device_type($ok_computers[0]);

		if ($type_1 == "cluster") {
			$computer_one = $_clu->get_cluster_name_by_id($computer_one);
		}

		$the_scan_name 	= "Scan of $computer_one";

		$stmt5->execute($username,$profile_id,$the_scan_name,'user');
	}
}

// Insert the progress meter item
$stmt6->execute($profile_id);

// The following populates the machines table
foreach($ok_computers as $key => $machine) {
	$stmt1->execute($profile_id, $machine);
}

/**
* Insert settings for new scan
*
* This code was taken directly from the async/settings.php file
* because the functionality that is needed here is identical to
* that provided by the do_save_specific_settings case in the
* settings page.
*
* I'm not happy with duplicating the code, but until I come up
* with a better way to use the settings page code, I'll just have
* to stick with this solution
*/
$settings['short_plugin_listing'] 	= import_var('short_plugin_listing', 'P');
$settings['ping_host_first']		= import_var('ping_host_first', 'P');
$settings['save_scan_report']		= import_var('save_scan_report', 'P');
$settings['report_format']		= import_var('report_format', 'P');
$settings['port_range']			= import_var('port_range', 'P');
$settings['recurring']			= import_var('recurrence', 'P');
$settings['scanner_id']			= import_var('scanner_id', 'P');

// max length of custom email subject is 128 characters
$settings['custom_email_subject']	= substr(import_var('custom_email_subject', 'P', 'email_subject'), 0, 128);

$recurrence['recur_type']		= import_var('recur_type', 'P');
		
// maxlength of the interval is 2 characters
$recurrence['the_interval']		= substr(import_var('the_interval', 'P'), 0, 2);

$recurrence['recur_on_day']		= import_var('recur_on_day', 'P');
$recurrence['recur_on_day_general']	= import_var('recur_on_day_general', 'P');
$recurrence['day_of_week']		= import_var('day_of_week', 'P');
$recurrence['days']			= import_var('days', 'P');
$recurrence['recur_on']			= import_var('recur_on', 'P');
$alternate_email_to			= import_var('alternate_email_to', 'P');
$alternate_cgibin			= import_var('alternate_cgibin', 'P');
$run_time				= strtolower(import_var('run_time','P'));
$recurring_run_time			= strtolower(import_var('recurring_run_time','P'));
$count					= 1;
$rules_string				= '';

if (count($alternate_email_to) < 1)
	$alternate_email_to = array();

if (count($alternate_cgibin) < 1)
	$alternate_cgibin = array();

/**
* This list contains the possible days in the week that a scan could be
* scheduled on. The list that is sent from the browser will be merged
* into this one, so days that are chosen will turn the values of the
* array into '1's
*/
$days_list	= array(
	'sun'	=> 0,
	'mon'	=> 0,
	'tue'	=> 0,
	'wed'	=> 0,
	'thu'	=> 0,
	'fri'	=> 0,
	'sat'	=> 0,
);

// Make the run time into a timestamp
$run_time = run_time_to_timestamp($run_time);

// Make the recurrence run time into a timestamp
if ($recurring_run_time != '') {
	$recurring_run_time = run_time_to_timestamp($recurring_run_time);
}

/**
* The format for the port range is #####-#####
* Check to make sure that if they have specified that format
* that it is actually correct
*/
$settings['port_range'] = check_port_range($settings['port_range']);

/**
* Clean the custom email subject if it was set. Otherwise set it
* back to the default value
*/
if ($settings['custom_email_subject'] == '') {
	$settings['custom_email_subject'] = "Nessus Scan";
}

// Code to update recurrence settings
if($settings['recurring'] == 1) {
	// Check to see the interval is an integer and if it
	// isnt, then assign a default of 7 (weekly)
	if (!is_numeric($recurrence['the_interval'])) {
		$recurrence['the_interval'] = 7;
	}

	if (count($recurrence['days']) < 1) {
		$recurrence['days'] = array('sun' => 1);
	}

	// Switch based on the recurrence type
	switch($recurrence['recur_type']) {
		case "D":
			// The day only requires the interval be set, so
			// if it is, then we can execute
			$stmt7->execute($profile_id,
					'D',
					$recurrence['the_interval'],
					$recurring_run_time,
					'',
					'D',
					$recurrence['the_interval'],
					$recurring_run_time,
					''
			);
			break;
		case "W":
			/**
			* If the user selected at least one day, then I should
			* process the list given to me. This will loop through
			* the default list of "all disabled" and will enable
			* a day if it is in the array sent from the form.
			*/
			foreach($days_list as $key => $val) {
				if(array_key_exists($key, $recurrence['days'])) {
					$days_list[$key] = 1;
				}
			}

			/**
			* Now that the array is properly "valued", make the recur
			* string and format it for weekly recurrence.
			*
			* Format of the string is
			* sun:1;mon:0;tue:0;wed:0;thu:0;fri:0;sat:0;
			*/
			foreach($days_list as $key => $val) {
				$rules_string .= $key.':'.$val.';';
			}

			// Run the SQL to add the recurrence entry
			$stmt7->execute($profile_id,
					'W',
					$recurrence['the_interval'],
					$recurring_run_time,
					$rules_string,
					'W',
					$recurrence['the_interval'],
					$recurring_run_time,
					$rules_string
			);
			break;
		case "M":
			if ($recurrence['recur_on'] == "gen") {
				$rules_string = "gen:".$recurrence['recur_on_day_general'].':'.$recurrence['day_of_week'];
				$stmt7->execute($profile_id,
						'M',
						$recurrence['the_interval'],
						$recurring_run_time,
						$rules_string,
						'M',
						$recurrence['the_interval'],
						$recurring_run_time,
						$rules_string
				);
			} else if ($recurrence['recur_on'] == "day") {
				$rules_string = "day:".$recurrence['recur_on_day'];
				$stmt7->execute($profile_id,
						'M',
						$recurrence['the_interval'],
						$recurring_run_time,
						$rules_string,
						'M',
						$recurrence['the_interval'],
						$recurring_run_time,
						$rules_string
				);
			}
			break;
		default:
			// By default error and return if the type
			// is not valid
			echo "fail::specific::$profile_id";
			return;
	}
}

// String together the alternate 'email to' list
$settings['alternative_email_list'] 	= make_alternate_email_to_list($alternate_email_to);

// String together the alternate 'cgi-bin' list
$settings['alternative_cgibin_list']	= make_alternate_cgibin_list($alternate_cgibin);

// Begin code to update the profile_settings table
foreach($settings as $key => $val) {
	$sql['update'] .= "$key = ':$count', ";
	$count += 1;
}

$sql['update'] = substr(trim($sql['update']),0,-1);
$sql['update'] .= " WHERE username = '$username' AND profile_id='$profile_id' AND setting_type='user'";

// Prepare the profile_settings update
$stmt9 = $db->prepare($sql['update']);
$stmt9->execute($settings);

// Update run time in case it has changed
$stmt8->execute($run_time, $profile_id);

/**
* Update the status of the scan and set it to pending. Up until
* this point, the status has been "not ready" to prevent the
* scan-maker from running the scan before the settings are saved
*/
$stmt10->execute('P',$profile_id);

/**
* By this point I've inserted the settings for this new profile and I'm done with
* processing. Hand the user a success message so that they can move on with what
* they were doing
*/
$output	= "<h5>Scan Scheduled</h5>"
. "You will receive an email with your scan results soon."
. "<p><a href='../index.php'>Schedule another scan</a>";

echo "<script type='text/javascript'>"
. "new Element.show('main_click');"
. "tmpd = document.getElementById('processing_steps').innerHTML;\n"
. "document.getElementById('processing_steps').innerHTML = tmpd + \"$output\""
. "</script>";
@ob_end_flush();
flush();

?>
