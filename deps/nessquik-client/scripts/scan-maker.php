#!/usr/bin/php -q

<?php

set_time_limit(0);

if (!@$argc) {
	die ("<p>script can only be run from command line");
}

define('_ABSPATH', dirname(dirname(__FILE__)));

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/lib/functions.php');
require_once(_ABSPATH.'/lib/scan-maker.php');
require_once(_ABSPATH.'/lib/Nessus.php');
require_once(_ABSPATH.'/lib/IXR_Library.php');
require_once(_ABSPATH.'/lib/Netmask.php');

$nes 	= new Nessus;
$_nm	= new Netmask;
$client = getIXRClient();

define_syslog_variables();

@$profile_id = $argv[1];

if ($profile_id == '') {
	exit;
}

// Begin processing the scan
$machine_list	= array();
$scanner_set	= array();
$output		= array();
$settings	= array();
$reading_output = false;
$stopped	= false;
$output		= '';
$recipients	= array();
$subject	= '';
$start_time	= '';
$end_time	= '';
$start_date	= '';
$end_date	= '';
$total_targets	= array();
$targets	= 0;
$max_progress	= 100;
$time		= array(
	'progress' 	=> time(),
	'cancel'	=> time()
);

$last_time 	= array(
	'progress'	=> '',
	'cancel'	=> ''
);

$start_time 	= strftime("%T", $time['progress']);
$start_date 	= strftime("%Y-%m-%d", $time['progress']);

// Get a list of all the plugins
$client->query('jobs.getAllPlugins');
$scanner_set = $client->getResponse();

// Get the profile settings for the profile being run
$client->query('jobs.getProfileSettings', _CLIENT_KEY, $profile_id);
$settings 	= $client->getResponse();

if (@$settings['faultCode']) {
	die($settings['faultString']."\n");
}

$format		= $settings['report_format'];
$machine_list 	= make_machine_list($profile_id);

if(count($machine_list) < 1) {
	syslog(LOG_ERR, "There are no machines listed in this scan profile: $profile_id");
	die("There are no machines listed in this scan profile: $profile_id");
}

// This prepares an array that will balance the output of the scan progress
foreach($machine_list as $key => $val) {
	/**
	* I need to check for cidrs and ranges here because otherwise the
	* total number of targets could be really small.
	*
	* example:
	*
	*	the target 111.222.111.0/24 is 1 target to nessquik
	*	but to Nessus it is 255-2 targets. The progress will
	*	involves 253 targets but the total would only be calculated
	*	off of 1 target. This same problem occurs with ranges.
	*/
	if (is_cidr($val)) {
		$_nm->init($val);
		$targets += $_nm->hosts_per_subnet();
	} else if (is_range($val)) {
		$tmp 		= explode('-', $val);
		$start_ip 	= $tmp[0];
		$end_ip		= $tmp[1];
		$cidrs 		= $_nm->range2cidrlist($start_ip, $end_ip);

		foreach($cidrs as $key => $val) {
			$_nm->init($val);
			$targets += $_nm->hosts_per_subnet();
		}
	} else {
		// Better be an IP address
		$targets += 1;
	}
}

$max_progress	= $targets * 100;

merge_severities($profile_id, $scanner_set);
merge_families($profile_id, $scanner_set);
merge_plugin_profiles($profile_id, $scanner_set);
merge_plugins($profile_id, $scanner_set);
merge_all($profile_id, $scanner_set);

// Make the machine list that specifies all the machines that need to be scanned
$ml_data	= get_ml_file_data($machine_list);
$ml		= make_ml_file($ml_data);

// Make the nessusrc file that contains scanner settings
$nrc_data 	= get_nrc_file_data($scanner_set, $settings);
$nrc 		= make_nrc_file($nrc_data);

// Update the status of the scan to Running
if(!$client->query('jobs.setStatus', _CLIENT_KEY, $profile_id, 'P', 'R')) {
	die($client->getErrorCode().' : '.$client->getErrorMessage());
}

// Build command to run in proc
$cmd = "nohup " . _NESSUS_CMD." -c $nrc -T nbe -x -V -q "._NESSUS_SERVER.' '._NESSUS_PORT.' '._NESSUS_USER.' '._NESSUS_PASS." $ml -";

// Set up array to hand to proc telling it how to handle std{in|out|err}
$descriptor_spec = array(
	0 => array('pipe', 'r'),
	1 => array('pipe', 'w'),
	2 => array('file', '/dev/null','a')
);

// Indexed array of file pointers that correspond to
// PHP's end of pipes that are created
$pipes = array();

// Now open the process and run the nessus command
$handle = proc_open($cmd, $descriptor_spec, $pipes);

// I'm setting $reading_source here as a way of abstracting the
// difference in data sources between php4 and php5
$reading_source = $pipes[1];

// die if the command failed
if (!is_resource($handle)) {
	syslog(LOG_ERR, "Couldn't run command $cmd");
	exit;
}

if (_DEBUG) {
	$timestamp = strftime("%y-%m-%d_%H-%M-%S", time());
	$dfh = fopen(_ABSPATH.'/logs/scan-maker-debug-'.$timestamp.'-'.mt_rand(0,10000).'.log', 'w');
	fwrite($dfh, "max_progress  total_scan_progress  total_attack_progress  current_progress  nessus_progress  time\n");
}

$portscan_total 	= 1;
$attack_total 		= 1;

// And here's where I read in the data from the open process
while(!feof($reading_source)) {
	if (!is_resource($handle)) {
		break;
	}

	/**
	* There's a chance that blank lines will be returned
	* in the output and I dont want to see them, so this
	* will skip past them
	*/
	$line = trim(fgets($reading_source,4096));
	if ($line == '') {
		continue;
	}

	$tmp 			= array();
	$total_scan_progress 	= 1;
	$total_attack_progress 	= 1;
	$total			= 1;
	$portscan_current	= 1;
	$attack_current		= 1;

	// Cut down the number of requests sent to update the webserver
	// to 1 every 10 seconds, or else the webserver can get overloaded
	if (time() >= ($time['cancel'] + 10)) {
		// Check to see if the process has been killed
		if(!$client->query('jobs.getCancel', $profile_id)) {
			die($client->getErrorCode().' : '.$client->getErrorMessage());
		}

		$result = $client->getResponse();

		$time['cancel'] = time();
	}

	// If the user has specified that the process be killed
	if ($result['cancel'] == 'Y') {
		// Kill the nessus process
		proc_terminate($handle,'9');

		// Update the profile table to say the process is
		// not running and set the kill bit back to N
		if(!$client->query('jobs.setResetCancel', _CLIENT_KEY, $profile_id)) {
			die($client->getErrorCode().' : '.$client->getErrorMessage());
		}

		// Clear the progress meter
		if(!$client->query('jobs.setProgress', _CLIENT_KEY, $profile_id, 0, 0)) {
			die($client->getErrorCode().' : '.$client->getErrorMessage());
		}

		if (_DEBUG) {
			fclose($dfh);
		}

		// Exit from the script. I might want to poll the
		// terminated process here to make sure it is indeed
		// dead before I exit.
		exit;
	}

	// Only care about the code below if I'm not reading the output
	if (!$reading_output) {
		if (strpos($line, "timestamps") !== false) {
			$reading_output = true;
			/**
			* I'm appending a triple colon here because the NBE
			* output contains newline (\n) characters. Therefore
			* I cant split on new lines. That's where the triple
			* comes in
			*/
			$output = str_replace('"',"'",$line).':::';

			continue;
		}

		// NBE is pipe delimited
		$tmp = explode('|', $line);

		$target		= $tmp[1];

		/**
		* In the progress NBE there are two type of progress that
		* I'm currently aware of; portscan and attack
		*
		*	attack|131.225.82.83|2|12248
		* 	portscan|131.225.82.83|100|4481
		*
		* The code needs to handle both of them, otherwise the
		* progress bar will appear to skip as the scan moves along
		*/
		if (strpos($line, "portscan") !== false) {
			$current 	= $tmp[2];
			$total		= $tmp[3];
			$type		= "scan";
		} else if (strpos($line, "attack") !== false) {
			$current	= $tmp[2];
			$total		= $tmp[3];
			$type		= "attack";
		}

		$progress = ($current / $total) * 100;

		$total_targets[$target][$type] = $progress;

		/**
		* Nessus operates differently than I originally thought.
		* The scan status is actually a per machine status.
		*/
		foreach($total_targets as $key => $individual_progress) {
			@$total_scan_progress 	+= $individual_progress["scan"];
			@$total_attack_progress	+= $individual_progress["attack"];
		}

		// Convert to a percentage
		$scan_progress 		= floor(($total_scan_progress / $max_progress) * 100);
		$attack_progress	= floor(($total_attack_progress / $max_progress) * 100);

		// Cut down the number of requests sent to update the webserver
		// to 1 every 10 seconds, or else the webserver can get overloaded
		if (time() >= ($time['progress'] + 10)) {
			// Update the progress on the server
			if(!$client->query('jobs.setProgress', _CLIENT_KEY, $profile_id, $scan_progress, $attack_progress)) {
				die($client->getErrorCode().' : '.$client->getErrorMessage());
			}
			$time['progress'] = time();
		}

		if (_DEBUG) {
			if ($line != '') {
				$the_time = strftime("%Y-%m-%d %T", time());
				fwrite($dfh, "$max_progress  $total_scan_progress  $total_attack_progress $progress  $line  $the_time\n");
			}
		}
	} else {
		// If I'm reading output, then just keep reading
		$output .= str_replace('"',"'",$line).':::';
	}
}

// Close the handle because no more data will be coming from it
proc_close($handle);

// Set progress to 100% because the scan is finished
if(!$client->query('jobs.setProgress', _CLIENT_KEY, $profile_id, 100, 100)) {
	die($client->getErrorCode().' : '.$client->getErrorMessage());
}

$time 		= time();
$end_time 	= strftime("%T", $time);
$end_date 	= strftime("%Y-%m-%d", $time);
$finished_date	= strftime("%Y-%m-%d %T", $time);

if (_DEBUG) {
	fwrite($dfh, "\n\nScan finished at $finished_date");
}

// Update the status of the scan to Finished
if(!$client->query('jobs.setStatus', _CLIENT_KEY, $profile_id, 'R', 'F')) {
	die($client->getErrorCode().' : '.$client->getErrorMessage());
}

if(!$client->query('jobs.setFinishedDate', _CLIENT_KEY, $profile_id, $finished_date)) {
	die($client->getErrorCode().' : '.$client->getErrorMessage());
}

// Save the report if asked to
if ($settings['save_scan_report']) {
	if(!$client->query('jobs.saveReport', _CLIENT_KEY, $profile_id, $finished_date, $output)) {
		die($client->getErrorCode().' : '.$client->getErrorMessage());
	}
}

// Make the list of alternate recipients
$recipients 	= explode(',', $settings['alternative_email_list']);
$recipients 	= array_unique($recipients);

// Create custom subject line if it was specified.
$subject 	= $settings['custom_email_subject'];

// The next couple blocks of code are replacing the available
// macros in the subject line
if (count($machine_list) > 2) {
	$subject = str_replace("%m", $machine_list[0].','.$ok_computers[1].'...', $subject);
} elseif (count($machine_list) > 1) {
	$subject = str_replace("%m", $machine_list[0].' and '.$machine_list[1], $subject);
} else {
	$subject = str_replace("%m", $machine_list[0], $subject);
}

$subject = str_replace("%D", $start_date, $subject);
$subject = str_replace("%d", $end_date, $subject);
$subject = str_replace("%T", $start_time, $subject);
$subject = str_replace("%t", $end_time, $subject);

if ($format == 'txt') {
	$output = $nes->output_text($output);
} else if ($format == 'html') {
	$output = $nes->output_html($output);
} else if ($format == 'nbe') {
	$output = $nes->output_nbe($output);
}

if (_DEBUG) {
	fwrite($dfh, "\nEmailing scan results");
}

// If there are multiple recipients, then they need to have email sent to them
if (count($recipients) > 0) {
	if(!$client->query('jobs.emailResults', _CLIENT_KEY, $profile_id, $recipients, $subject, $output, $format)) {
		die($client->getErrorCode().' : '.$client->getErrorMessage());
	}
} else {
	if(!$client->query('jobs.emailResults', _CLIENT_KEY, $profile_id, '', $subject, $output, $format)) {
		die($client->getErrorCode().' : '.$client->getErrorMessage());
	}
}

// Get duration of the scan to insert into the exempt database
$duration = $nes->call_duration_seconds($nes->scan_start,$nes->scan_end);

if (_RELEASE == "fermi") {
	if(!$client->query('jobs.addExemption', _CLIENT_KEY, $profile_id, $username, $duration)) {
		die($client->getErrorCode().' : '.$client->getErrorMessage());
	}

	if (_DEBUG) {
		fwrite($dfh, "\nInserting scan exemption");
	}
}

// Close the log file handle
if (_DEBUG) {
	fclose($dfh);
}

// And remove the machine list...
if(file_exists($ml)) {
	unlink($ml);
}

// ...and nessusrc files
if(file_exists($nrc)) {
	unlink($nrc);
}

?>
