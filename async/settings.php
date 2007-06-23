<?php

session_name('nessquik');
session_start();

// Used for including files
if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(__FILE__)));
}

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/lib/settings.php');
require_once(_ABSPATH.'/lib/Smarty.php');
require_once(_ABSPATH.'/lib/functions.php');
require_once(_ABSPATH.'/db/nessquikDB.php');

$db 	= nessquikDB::getInstance();
$tpl	= SmartyTemplate::getInstance();

$tpl->template_dir      = _ABSPATH.'/templates/';
$tpl->compile_dir	= _ABSPATH.'/templates_c/';

$action = import_var('action', 'P');

switch($action) {
	case "x_show_user_settings":
		require_once(_ABSPATH.'/lib/User.php');

		$_usr = User::getInstance();

		$sql = array(
			'select' => "	SELECT * 
					FROM profile_settings 
					WHERE username=':1' 
					AND setting_type='sys';",
			'all_groups' => "SELECT group_id FROM division_group_list WHERE group_name='All Groups';",
			'scanners' => "	SELECT * 
					FROM scanners AS scn 
					LEFT JOIN scanners_groups AS sg 
					ON sg.scanner_id=scn.scanner_id 
					WHERE sg.group_id=':1' 
					OR sg.group_id=':2'"
		
		);
		$username 	= import_var('username', 'P');
		$division	= $_usr->get_division_from_uid($username);
		$division_id	= $_usr->get_division_id($division);
		$ael_count	= 0;
		$acl_count 	= 0;
		$scanners	= array();

		$stmt1 = $db->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['all_groups']);
		$stmt3 = $db->prepare($sql['scanners']);

		$stmt1->execute($username);

		$stmt2->execute();
		$all_groups = $stmt2->result(0);

		$stmt3->execute($division_id, $all_groups);

		while($row = $stmt3->fetch_assoc()) {
			$scanners[] = array(
				'id'	=> $row['scanner_id'],
				'name' 	=> $row['name']
			);
		}

		if ($stmt1->num_rows() < 1) {
			echo "fail";
		} else {
			$row = $stmt1->fetch_assoc();

			// Split up the alternate email addresses
			if ($row['alternative_email_list'] == '') {
				$alternative_email_list = array();

				/**
				* Setting to 1 to get around the problem of already
				* having an empty field if no alternate emails are specified
				*/
				$ael_count = 1;
			} else {
				// I save the email list as a colon separated string
				$tmp = explode(':',$row['alternative_email_list']);

				/**
				* Each email address will go into an array that will
				* be used by Smarty in a section block to print out
				* text fields
				*/
				foreach($tmp as $key => $val) {
					$alternative_email_list[] = $val;
					$ael_count += 1;
				}
			}

			// Split up the alternate cgibin directories
			if ($row['alternative_cgibin_list'] == '') {
				/**
				* Setting it to an empty array will make Smarty
				* deal with the section correctly
				*/
				$alternative_cgibin_list = array();

				/**
				* and make the count == 1 because by default
				* 1 empty field will be shown on the page
				*/
				$acl_count = 1;
			} else {
				$tmp = explode(':', $row['alternative_cgibin_list']);
	
				foreach($tmp as $key => $val) {
					$alternative_cgibin_list[] = $val;
					$acl_count += 1;
				}
			}

			// Assign all the data for the template to template variables
			$tpl->assign(array(
				'short_plugin_listing' 		=> $row['short_plugin_listing'],
				'ping_host_first'		=> $row['ping_host_first'],
				'report_format'			=> $row['report_format'],
				'save_scan_report'		=> $row['save_scan_report'],
				'port_range'			=> $row['port_range'],
				'custom_email_subject'		=> $row['custom_email_subject'],
				'scanner_id'			=> $row['scanner_id'],

				'alternative_email_list'	=> $alternative_email_list,
				'ael_count'			=> $ael_count,
				'alternative_cgibin_list'	=> $alternative_cgibin_list,
				'acl_count'			=> $acl_count,
				'scanners'			=> $scanners
			));

			$tpl->display('settings_general.tpl');
		}
		break;
	case "do_save_settings":
		$settings['short_plugin_listing'] 	= import_var('short_plugin_listing', 'P');
		$settings['ping_host_first']		= import_var('ping_host_first', 'P');
		$settings['report_format']		= import_var('report_format', 'P');
		$settings['port_range']			= import_var('port_range', 'P');
		$settings['save_scan_report']		= import_var('save_scan_report', 'P');
		$settings['scanner_id']			= import_var('scanner_id', 'P');

		// max length of custom email subject is 128 characters
		$settings['custom_email_subject']	= substr(import_var('custom_email_subject', 'P', 'email_subject'), 0, 128);

		$alternate_email_to			= import_var('alternate_email_to', 'P', 'email_addy');
		$alternate_cgibin			= import_var('alternate_cgibin', 'P', 'cgibin');
		$username 				= import_var('username', 'S');
		$count					= 1;

		if (count($alternate_email_to) < 1) {
			$alternate_email_to = array();
		}

		if (count($alternate_cgibin) < 1) {
			$alternate_cgibin = array();
		}

		$settings['port_range'] = check_port_range($settings['port_range']);

		/**
		* Clean the custom email subject if it was set. Otherwise set it
		* back to the default value
		*/
		if ($settings['custom_email_subject'] == '') {
			$settings['custom_email_subject'] = "Nessus Scan";
		}

		// String together the alternate 'email to' list
		$settings['alternative_email_list'] 	= make_alternate_email_to_list($alternate_email_to);

		// String together the alternate 'cgi-bin' list
		$settings['alternative_cgibin_list']	= make_alternate_cgibin_list($alternate_cgibin);

		/**
		* Hack together the SQL update query. I really dont like doing it
		* this way because it looks messy and because it's only halfway to
		* becomming automated (add new setting and have to change no code)
		*/
		$sql = array(
			'update' => "UPDATE profile_settings SET "
		);

		foreach($settings as $key => $val) {
			$sql['update'] .= "$key = ':$count', ";
			$count += 1;
		}
		
		$sql['update'] = substr(trim($sql['update']),0,-1);
		$sql['update'] .= " WHERE username = '$username' AND setting_name='0' AND setting_type='sys'";

		$stmt1 = $db->prepare($sql['update']);
		$stmt1->execute($settings);
		echo "pass::general::$username";
		break;
	case "x_show_scan_settings":
		$username 	= import_var('username','S');
		$scans		= array();
		$sql = array(
			'select' => "SELECT ust.setting_id, 
					ust.profile_id, 
					ust.setting_name, 
					pl.status
				FROM profile_settings AS ust 
				LEFT JOIN profile_list AS pl 
				ON pl.profile_id = ust.profile_id
				WHERE pl.username=':1' 
				AND ust.setting_type = 'user';"
		);

		$stmt1 = $db->prepare($sql['select']);

		$stmt1->execute($username);

		if ($stmt1->num_rows() < 1) {
			echo "<div style='text-align: center;'>You haven't saved any scans yet.</div><br>";
		} else {
			while($row = $stmt1->fetch_assoc()) {
				$scans[] = array(
					'setting_id' 	=> $row['setting_id'],
					'profile_id' 	=> $row['profile_id'],
					'name'		=> $row['setting_name'],
					'status'	=> $row['status']
				);
			}

			$tpl->assign('scans', $scans);
			$tpl->display('settings_specific.tpl');
		}
		break;
	case "x_do_get_specific_scan_settings":
		require_once(_ABSPATH.'/lib/User.php');

		$_usr			= User::getInstance();

		$profile_id 		= import_var('profile_id', 'P');
		$username 		= import_var('username', 'S');
		$division		= $_usr->get_division_from_uid($username);
		$division_id		= $_usr->get_division_id($division);
		$scan			= array();
		$severities		= array();
		$families		= array();
		$plugins		= array();
		$user_nth_day		= '';
		$user_nth_day_general 	= '';
		$all			= '0';
		$type 			= "W";
		$the_interval 		= 1;
		$rules_string 		= "sun:1;mon:0;tue:0;wed:0;thu:0;fri:0;sat:0;";
		$days 			= days_data($rules_string);
		$acl_count 		= 0;
		$ael_count		= 0;
		$plugin_count		= 0;
		$scanners		= array();
		$vhosts			= array();
		$has_whitelist		= false;
		$has_saved_scans	= false;
		$has_special_plugins	= false;
		$has_clusters		= false;

		$sql = array(
			'select' => "	SELECT * 
					FROM profile_settings 
					WHERE profile_id=':1' 
					AND username=':2';",

			'machines' => "	SELECT * 
					FROM profile_machine_list 
					WHERE profile_id=':1'",

			'recur' => "	SELECT * 
					FROM recurrence 
					WHERE profile_id=':1'",

			'schedule' => "	SELECT date_scheduled 
					FROM profile_list 
					WHERE profile_id=':1'",

			'plugins' => "	SELECT COUNT(*) 
					FROM profile_plugin_list 
					WHERE profile_id=':1'",

			'all_groups' => "SELECT group_id 
					FROM division_group_list 
					WHERE group_name='All Groups';",

			'scanners' => "	SELECT * 
					FROM scanners AS scn 
					LEFT JOIN scanners_groups AS sg 
					ON sg.scanner_id=scn.scanner_id 
					WHERE sg.group_id=':1' 
					OR sg.group_id=':2'"
		);

		$stmt1 = $db->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['machines']);
		$stmt3 = $db->prepare($sql['plugins']);
		$stmt4 = $db->prepare($sql['recur']);
		$stmt5 = $db->prepare($sql['schedule']);
		$stmt7 = $db->prepare($sql['all_groups']);
		$stmt8 = $db->prepare($sql['scanners']);

		$stmt1->execute($profile_id,$username);
		$settings = $stmt1->fetch_assoc();

		$stmt2->execute($profile_id);
		$stmt3->execute($profile_id);
		$stmt5->execute($profile_id);

		// Get the "all groups" id
		$stmt7->execute();
		$all_groups = $stmt7->result(0);

		$stmt8->execute($division_id, $all_groups);
		while($row = $stmt8->fetch_assoc()) {
			$scanners[] = array(
				'id'	=> $row['scanner_id'],
				'name' 	=> $row['name']
			);
		}

		$plugin_count = $stmt3->result(0);

		if ($settings['setting_type'] == "sys") {
			$date_scheduled = strtotime("now");
		} else {
			$date_scheduled	= $stmt5->result(0);
			$date_scheduled	= strtotime($date_scheduled);
		}

		/**
		* If the scan is a recurring scan, then the recurrence settings
		* must be gleamed from the database. Otherwise, a default recurrence
		* of weekly will be assigned
		*/
		if ($settings['recurring'] == '1') {
			$stmt4->execute($profile_id);

			$row2 		= $stmt4->fetch_assoc();
			$type 		= $row2['recur_type'];
			$the_interval 	= $row2['the_interval'];

			if ($row2['specific_time'] == '')
				$row2['specific_time'] = strftime("%Y-%m-%d 16:00:00", time());

			$specific_time	= strtotime($row2['specific_time']);

			if ($type == 'D') {
				$days = days_data($rules_string);
			} elseif ($type == 'W') {
				/**
				* Work with the weekly recur string. The format
				* of this string is below.
				*
				* sun:0;mon:0;tue:0;wed:0;thu:0;fri:0;sat:0;
				*
				* The string is semicolon (;) delimited.
				* The first two characters are the day of the week
				* A colon separates the day from the value specifying
				* whether it is(1) or is not(0) scheduled for that day
				*/
				$rules_string = $row2['rules_string'];

				// By default, Sunday is the chosen day for a week based scan
				if ($rules_string == '') {
					$rules_string = "sun:1;mon:0;tue:0;wed:0;thu:0;fri:0;sat:0;";
				}
				
				$days = days_data($rules_string);
			} else if ($type == 'M') {
				$rules_string = $row2['rules_string'];

				$tmp = explode(':',$rules_string);

				if ($tmp[0] == "gen") {
					$tpl->assign('user_nth_day_general', $tmp[1]);
					$tpl->assign('user_weekday', $tmp[2]);
				} else if ($tmp[0] == "day")
					$tpl->assign('user_nth_day', $tmp[1]);
			}
		} else {
			$specific_time	= strftime("%Y-%m-%d 11:00:00", time());
			$specific_time	= strtotime($specific_time);

			// $rules_string is defined at the top of this switch statement
			$days = days_data($rules_string);
		}

		/**
		* Make the array of nth dates that are going to be used
		* in the first monthly drop down box
		*/
		for($x = 1; $x <= 31; $x++) {
			$name = '';

			/**
			* This set of if statements is kinda a kludge to
			* suffix the digits with suffixes that are correct.
			* Frankly I know no better way of accomplishing this
			*/
			if ($x == 1 || $x == 21 || $x == 31) {
				$name = $x."st";
			} else if ($x == 2 || $x == 22) {
				$name = $x."nd";
			} else if ($x == 3 || $x == 23) {
				$name = $x."rd";
			} else {
				$name = $x."th";
			}

			$nth_days[] = array(
				'val'	=> $x,
				'name'	=> $name
			);
		}

		// Make array of nth_days_general
		$nth_days_general[] = array('val' => "1st", 'name' => "1st");
		$nth_days_general[] = array('val' => "2nd", 'name' => "2nd");
		$nth_days_general[] = array('val' => "3rd", 'name' => "3rd");
		$nth_days_general[] = array('val' => "4th", 'name' => "4th");
		$nth_days_general[] = array('val' => "last", 'name' => "Last");
		$nth_days_general[] = array('val' => "2_last", 'name' => "2nd to Last");

		// Split up the alternate email addresses
		if ($settings['alternative_email_list'] == '') {
			$alternative_email_list = array();
			$ael_count = 1;
		} else {
			$tmp = explode(',',$settings['alternative_email_list']);

			foreach($tmp as $key => $val) {
				$alternative_email_list[] = $val;
				$ael_count += 1;
			}
		}

		// Split up the alternate cgibin directories
		if ($settings['alternative_cgibin_list'] == '') {
			/**
			* If the alternative cgi-bin list is empty
			* and the setting type is a temp setting
			* this means that the user is likely creating
			* a new profile, so add some default cgi-bin
			* lists
			*/
			if ($settings['setting_type'] == "sys") {
				$alternative_cgibin_list = array(
					'/cgi-bin',
					'/scripts'
				);
			} else {
				$alternative_cgibin_list = array();
			}

			$acl_count = 1;
		} else {
			$tmp = explode(':', $settings['alternative_cgibin_list']);

			foreach($tmp as $key => $val) {
				$alternative_cgibin_list[] = $val;
				$acl_count += 1;
			}
		}

		$device_counter = $stmt2->num_rows();

		$has_whitelist 		= ($_usr->has_whitelist($username)) 		? true : false;
		$has_special_plugins	= ($_usr->has_special_plugins($division_id))	? true : false;
		$has_clusters		= ($_usr->has_clusters($username))		? true : false;
		$has_registered		= ($_usr->has_registered($username)) 		? true : false;
		$vhosts			= $_usr->get_vhosts($username);

		// Assign template variables
		$tpl->assign(array(
			'profile_id'			=> $settings['profile_id'],
			'scan_name'			=> ($settings['setting_type'] == "sys") ? "new scan" : $settings['setting_name'],
			'setting_id'			=> $settings['setting_id'],
			'ping_host_first' 		=> $settings['ping_host_first'],
			'report_format'			=> $settings['report_format'],
			'save_scan_report'		=> $settings['save_scan_report'],
			'port_range'			=> $settings['port_range'],
			'recurrence'			=> $settings['recurring'],
			'custom_email_subject'		=> $settings['custom_email_subject'],
			'scanner_id'			=> $settings['scanner_id'],

			'month'				=> strftime('%B', $date_scheduled),
			'day'				=> strftime('%d', $date_scheduled),
			'year'				=> strftime('%Y', $date_scheduled),
			'hour'				=> strftime('%I', $date_scheduled),
			'minute'			=> strftime('%M', $date_scheduled),
			'ampm'				=> strftime('%p', $date_scheduled),

			'device_counter'		=> $device_counter,
			'month_input'			=> strftime('%Y-%m-%d %I:%M %p', $date_scheduled),
			'the_interval'			=> $the_interval,
			'days'				=> $days,
			'nth_days'			=> $nth_days,
			'nth_days_general'		=> $nth_days_general,
			'recur_type'			=> $type,
			'alternative_email_list'	=> $alternative_email_list,
			'ael_count'			=> $ael_count,
			'alternative_cgibin_list'	=> $alternative_cgibin_list,
			'acl_count'			=> $acl_count,
			'plugin_count'			=> $plugin_count,
			'scanners'			=> $scanners,
			'vhosts'			=> $vhosts,

			'time'				=> strftime('%I:%M %p', $specific_time),
			'time_input'			=> strftime('%Y-%m-%d %I:%M %p', $specific_time),
			'clock_hour'			=> strftime('%I', $specific_time),
			'clock_minute'			=> strftime('%M', $specific_time),
			'clock_ampm'			=> strftime('%p', $specific_time),

			'HAS_WHITELIST'		=> $has_whitelist,
			'HAS_SPECIAL_PLUGINS'	=> $has_special_plugins,
			'HAS_CLUSTERS'		=> $has_clusters,
			'HAS_REGISTERED_COMPS'	=> $has_registered
		));

		$tpl->display('settings_specific_scan.tpl');
		break;
	case "do_save_specific_settings":
		require_once(_ABSPATH.'/lib/process.php');
		require_once(_ABSPATH.'/lib/User.php');

		$_usr = User::getInstance();

		/**
		* The list of all the selected plugins for this scan
		*
		* @var array
		*/
		$item					= import_var('item','P');

		/**
		* The list of all the selected devices for this scan
		* @var array
		*/
		$devs					= import_var('dev', 'P');

		if (!is_array($devs)) {
			$devs	= array();
		}

		// Textarea list of machines
		$list					= str_replace(' ','',trim(import_var('list', 'P')));

		/**
		* In case the user removed some of the javascript to clear the list, I should
		* check to make sure no funniness has been happening
		*/
		if ($list == "click here to enter a list of computers") {
			$list = '';
		} else if ($list == "clickheretoenteralistofcomputers") {
			$list = '';
		}

		$settings['setting_name']		= import_var('scan_name', 'P', 'scan_name');
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
		$profile_id				= import_var('profile_id', 'P');
		$username 				= import_var('username','S');
		$user_id				= $_usr->get_id($username);
		$count					= 1;
		$rules_string				= '';
		$ok_computers				= array();

		if ($list != '') {
			$whitelist	= $_usr->get_whitelist($username);
		}

		if (count($alternate_email_to) < 1) {
			$alternate_email_to = array();
		}

		if (count($alternate_cgibin) < 1) {
			$alternate_cgibin = array();
		}

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

		// Begin making SQL string
		$sql = array(
			'status' => "SELECT status FROM profile_list WHERE profile_id=':1'",
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
			'del_recur' => "DELETE FROM recurrence 
					WHERE profile_id=':1'",
			'update_time' => "UPDATE profile_list 
					SET date_scheduled=':1' 
					WHERE profile_id=':2';",
			'add_plugins' => "INSERT INTO profile_plugin_list (
					`profile_id`,
					`plugin_type`,
					`plugin`) VALUES (':1',':2',':3');",
			'del_plugins' => "DELETE FROM profile_plugin_list
					WHERE profile_id=':1';",
			'del_machine' => "DELETE FROM profile_machine_list
					WHERE profile_id=':1';",
			'add_machine' => "INSERT INTO profile_machine_list (
					`profile_id`,
					`machine`)
					VALUES (':1',':2');",
		);

		// Make the run time into a timestamp
		$run_time = run_time_to_timestamp($run_time);

		// Make the recurrence run time into a timestamp
		if ($recurring_run_time != '') {
			$recurring_run_time = run_time_to_timestamp($recurring_run_time);
		}

		// $stmt1 is prepared further down than the canned queries here
		$stmt2 = $db->prepare($sql['status']);
		$stmt3 = $db->prepare($sql['recurrence']);
		$stmt4 = $db->prepare($sql['del_recur']);
		$stmt5 = $db->prepare($sql['update_time']);

		// SQL needed for plugin modifications
		$stmt6 = $db->prepare($sql['add_plugins']);
		$stmt7 = $db->prepare($sql['del_plugins']);

		// SQL needed for device modifications
		$stmt8 = $db->prepare($sql['del_machine']);
		$stmt9 = $db->prepare($sql['add_machine']);

		// Check the status to make sure it's not running.
		// This makes sure that users arent being sneaky
		$stmt2->execute($profile_id);
		$status = $stmt2->result(0);

		if ($status == 'R') {
			echo "fail::specific::$profile_id::isrunning";
			return;
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

			if ($recurrence['the_interval'] < 1) {
				$recurrence['the_interval'] = 1;
			}

			if (count($recurrence['days']) < 1) {
				$recurrence['days'] = array('sun' => 1);
			}

			// Switch based on the recurrence type
			switch($recurrence['recur_type']) {
				case "D":
					// The day only requires the interval be set, so
					// if it is, then we can execute
					$stmt3->execute($profile_id,
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
					$stmt3->execute($profile_id,
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
						$stmt3->execute($profile_id,
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
						$stmt3->execute($profile_id,
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
		} else {
			/**
			* Delete the recurrence details if the box was left
			* unchecked. I'm assuming the user meant to say they
			* dont want the scan to recur anymore.
			*/
			$stmt4->execute($profile_id);
		}

		$settings['alternative_email_list'] 	= make_alternate_email_to_list($alternate_email_to);
		$settings['alternative_cgibin_list']	= make_alternate_cgibin_list($alternate_cgibin);

		// Begin code to update the profile_settings table
		foreach($settings as $key => $val) {
			$sql['update'] .= "$key = ':$count', ";
			$count += 1;
		}
		
		$sql['update'] = substr(trim($sql['update']),0,-1);
		$sql['update'] .= " WHERE username = '$username' AND profile_id='$profile_id' AND setting_type='user'";

		// Prepare the profile_settings update
		$stmt1 = $db->prepare($sql['update']);
		$stmt1->execute($settings);

		// Update run time in case it has changed
		$stmt5->execute($run_time, $profile_id);

		// Update the plugin list. First, delete all the existing plugins
		$stmt7->execute($profile_id);

		if (count($item) > 0) {
			// Now re-add the plugins. The following populates the plugins table
			if (in_array("a:all",$item)) {
				$stmt6->execute($profile_id, 'all', 'all');
			} else {
				foreach ($item as $key => $val) {
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
						$stmt6->execute($profile_id, 'sev', $item);

					// Family items are prefixed with an 'f:'
					} else if ($prefix == 'f') {
						// Remove the prefix before inserting into table
						$stmt6->execute($profile_id, 'fam', $item);

					// Regular plugins are prefixed with a 'p:'
					} else if ($prefix == 'p') {
						// Remove the prefix before inserting into table
						$stmt6->execute($profile_id, 'plu', $item);

					// Special plugin profiles are prefixed with a 'sp:'
					} else if ($prefix == 'sp') {
						$stmt6->execute($profile_id, 'spe', $item);
					}
				}
			}
		}

		// Update the machine list. First, delete all the existing machines
		$stmt8->execute($profile_id);

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
					$accepted_list	= $_usr->get_accepted_machine_list($user_id);

					// Run through the whitelist one more time using the hostnames
					// or ips to see if they are in the whitelist
					check_registered_host_or_ip_in_list($accepted_list, $ok_computers, $machine_list);
				}
			} else {
				foreach($machine_list as $key => $val) {
					$ok_computers[] = ":gen:$val";
				}
				$machine_list = array();
			}

			if (count($ok_computers) < 1) {
				echo "no_perms::specific::$profile_id";
				return;
			}
		}

		// Filter out empty elements
		$ok_computers 	= array_filter($ok_computers, "strip_empty");
		$computers 	= array_merge($ok_computers, $devs);

		// Remove any duplicates (may be possible);
		$computers 	= array_unique($computers);

		if (count($computers) > 0) {
			// Now re-add the machines. The following populates the machines table
			foreach($computers as $key => $machine) {
				$stmt9->execute($profile_id, $machine);
			}
		}

		// Return success
		echo "pass::specific::$profile_id";
		break;
	case "x_do_get_specific_scan_plugins":
		$profile_id 	= import_var('profile_id','P');
		$plugin_profile	= import_var('plugin_profile', 'P');
		$count		= 1;
		$plugins	= array();
		$sql = array(
			'plugins' => "SELECT * FROM profile_plugin_list WHERE profile_id=':1';",
			'disp' => "SELECT shortdesc FROM plugins WHERE pluginid=':1';",
			'special_plugin' => "SELECT profile_name FROM special_plugin_profile WHERE profile_id=':1';",
			'profile_plugins' => "SELECT * FROM special_plugin_profile_items WHERE profile_id=':1';",
		);

		if ($plugin_profile == '') {
			$stmt1 = $db->prepare($sql['plugins']);
		} else {
			$stmt1 = $db->prepare($sql['profile_plugins']);
		}

		$stmt2 = $db->prepare($sql['disp']);
		$stmt3 = $db->prepare($sql['special_plugin']);

		$stmt1->execute($profile_id);

		// Get a list of all the plugins for this saved scan
		while($row = $stmt1->fetch_assoc()) {
			$display	= '';
			$plugin_type 	= $row['plugin_type'];
			$plugin_name 	= $row['plugin'];

			if ($plugin_type == 'plu') {
				// straight up plugin names are stored as their plugin ids
				$stmt2->execute($row['plugin']);
				$display = $stmt2->result(0);
			} else if ($plugin_type == 'spe') {
				// and special plugin profile name are stored in a different table
				$stmt3->execute($row['plugin']);
				$display = $stmt3->result(0);
			} else {
				$display = $row['plugin'];
			}

			$plugins[] = array(
				'id'	=> $count,
				'name'	=> $plugin_name,
				'type'	=> $plugin_type,
				'disp'	=> $display,
				'val'	=> $plugin_name
			);

			$count++;
		}

		$tpl->assign('plugins',$plugins);
		$tpl->display('settings_specific_scan_plugins.tpl');
		break;
	case "x_do_get_specific_scan_devices":
		require_once(_ABSPATH.'/lib/Clusters.php');
		require_once(_ABSPATH.'/lib/Devices.php');

		$_clu	= Clusters::getInstance();
		$_dev	= Devices::getInstance();

		$profile_id 	= import_var('profile_id','P');
		$count		= 1;
		$devices	= array();
		$sql = array(
			'devices' => "SELECT * FROM profile_machine_list WHERE profile_id=':1';"
		);

		$stmt1 = $db->prepare($sql['devices']);
		$stmt1->execute($profile_id);

		while($row = $stmt1->fetch_assoc()) {
			$type 	= '';
			$device	= '';

			// determine the device type here; cluster,registered,etc
			$type = $_dev->determine_device_type($row['machine']);

			// strip off the device type to only get back the device name
			// aka the cluster name, whitelist entry, vhost, etc
			$device = $_dev->strip_device_type($row['machine']);

			/**
			* Cluster IDs are saved to the machine list
			*
			*	ex.
			*		:clu:534677
			*
			* Therefore to make the saved clusters appear nice, I need
			* to transform their displayed value back into a cluster name
			*/
			if ($type == "cluster") {
				$device = $_clu->get_cluster_name_by_id($device);
			}

			$devices[] = array(
				'id'		=> $row['row_id'],
				'val'		=> $row['machine'],
				'type'		=> $type,
				'device'	=> $device,
				'count'		=> $count
			);

			$count++;
		}

		$tpl->assign('devices',$devices);
		$tpl->display('settings_specific_scan_devices.tpl');
		break;
}

?>
