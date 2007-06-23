<?php

error_reporting(0);
set_time_limit(0);

session_name('nessquik');
session_start();

require_once('../confs/config-inc.php');
require_once(_ABSPATH.'/lib/Smarty.php');
require_once(_ABSPATH.'/lib/functions.php');
require_once(_ABSPATH.'/db/nessquikDB.php');

$profile_id = $_SESSION['profile_id'];

if (!is_numeric($profile_id)) {
	die("You'll need to create a scan profile first.");
}

$db 	= nessquikDB::getInstance();
$tpl	= SmartyTemplate::getInstance();

$tpl->template_dir      = ABSPATH.'/templates/';
$tpl->compile_dir	= ABSPATH.'/templates_c/';
$tpl->assign('step', 1);
$tpl->assign('action', "pre");
$tpl->display('results.tpl');
@ob_end_flush();
flush();

$sql = array(
	'profile_info' => "SELECT profile_id, username FROM profile_list WHERE profile_id=':1'",
	'machine_list' => "SELECT machine FROM profile_machine_list WHERE profile_id=':1'",
	'profile_plugins' => "SELECT plugin_type,plugin FROM profile_plugin_list WHERE profile_id=':1'",
	'plug_by_family' => "SELECT pluginid FROM plugins WHERE family=':1' ORDER BY pluginid ASC",
	'plug_by_severity' => "SELECT pluginid FROM plugins WHERE sev=':1' ORDER BY pluginid ASC",
	'plug_all' => "SELECT pluginid FROM plugins ORDER BY pluginid ASC",

	'del_profile_info' => "DELETE FROM profile_list WHERE profile_id=':1'",
	'del_plugin_info' => "DELETE FROM profile_plugin_list WHERE profile_id=':1'",
	'del_machine_info' => "DELETE FROM profile_machine_list WHERE profile_id=':1'"
);

$stmt = $db->prepare($sql['plug_all']);
$stmt2 = $db->prepare($sql['profile_info']);

$stmt3 = $db->prepare($sql['del_profile_info']);
$stmt4 = $db->prepare($sql['del_plugin_info']);
$stmt5 = $db->prepare($sql['del_machine_info']);

/**
* Nessus enables plugins by default. Doing this loop will set all
* the plugins to disabled by default. After this, I'll go through
* and one by one re-enable the plugins
*/
$stmt->execute();
while($row = $stmt->fetch_assoc()) {
	$scanner_set[$row['pluginid']] = 'no';
}

$set = $scanner_set;

// Get the information for all the profiles
$stmt2->execute($profile_id);

// Loop through each profile's information
$row = $stmt2->fetch_assoc();

$machine_list	= array();
$username	= $row['username'];
$rand		= rand(0,1000000);
$scanner_set	= $set;
$output		= array();
$machine_list 	= make_machine_list($profile_id);

merge_severities($profile_id, $scanner_set);
merge_all($profile_id, $scanner_set);
merge_families($profile_id, $scanner_set);
merge_plugins($profile_id, $scanner_set);

$ml = make_ml_file($username, $rand, $machine_list);
$nrc = make_nrc_file($username, $rand, $scanner_set);

$stmt3->execute($profile_id);
$stmt4->execute($profile_id);
$stmt5->execute($profile_id);

$command = escapeshellcmd("nohup "._NESSUS_CMD." -q -c $nrc -T html -x "._NESSUS_SERVER.' '._NESSUS_PORT.' '._NESSUS_USER.' '._NESSUS_PASS." $ml -");

exec($command, $output);

if (count($output) < 1)
	$retval = "The scan was not run. Did you remeber to give appropriate sudo privileges to the web user?";
else {
	foreach($output as $key => $val) {
		$val = trim($val);
		if($val == '')
			$retval .= ' ';
		else if (strpos(strtolower($val), "<!doctype") !== false)
			continue;
		else if (strpos(strtolower($val), "<head>") !== false)
			continue;
		else if (strpos(strtolower($val), "<title>") !== false)
			continue;
		else if (strpos(strtolower($val), "</title>") !== false)
			continue;
		else if (strpos(strtolower($val), "</head>") !== false)
			continue;
		else if (strpos(strtolower($val), "<meta") !== false)
			continue;
		else if (strpos(strtolower($val), "<html>") !== false)
			continue;
		else if (strpos(strtolower($val), "</html>") !== false)
			continue;
		else if (strpos(strtolower($val), "<body>") !== false)
			continue;
		else if (strpos(strtolower($val), "</body>") !== false)
			continue;
		else if ((strpos(strtolower($val), "<table") !== false) && (strpos($val, 'width=') !== false)) {
			$val = preg_replace('/width\=\"[0-9]+\%\"/', "width='100%'", $val);
			$val = str_replace('"', "'", $val);
			$retval .= $val;
		} else {
			$val = str_replace('"', "'", $val);
			$retval .= $val;
		}
	}
}

if(file_exists($ml))
	unlink($ml);

if(file_exists($nrc))
	unlink($nrc);

$tpl->assign('action', "post");
$tpl->assign('results', $retval);

echo "<script type='text/javascript'>"
. "document.getElementById('pre_results').style.display = 'none';"
. "</script>";

$tpl->display('results.tpl');

@ob_end_flush();
flush();

function make_ml_file($username, $rand, $machine_list) {
	if (!$username)
		$filename = _NESSUS_STORE . '/' . $rand.".ml";
	else
		$filename = _NESSUS_STORE . '/' . $username.'-'.$rand.".ml";

	$fh = fopen($filename, "w");

	// do an exclusive lock
	if (flock($fh, LOCK_EX)) {
		foreach($machine_list as $key => $val) {
			fwrite($fh, $val."\n");
		}

		flock($fh, LOCK_UN); // release the lock
	} else {
		define_syslog_variables();
		syslog(LOG_WARNING, "Couldn't lock the machine list file !");
	}

	fclose($fh);

	chmod($filename, 0777);
	return $filename;
}

function make_nrc_file($username, $rand, $unique_list) {
	if (!$username)
		$filename = _NESSUS_STORE . '/' . $rand.".nrc";
	else
		$filename = _NESSUS_STORE . '/' . $username.'-'.$rand.".nrc";

	$fh = fopen($filename, "w");

	// do an exclusive lock
	if (flock($fh, LOCK_EX)) {
		// Write a common header
		fwrite($fh,"# Nessus Client Preferences File\n");

		// This defines the start of the scanner list
		fwrite($fh,"begin(SCANNER_SET)\n");

		foreach($unique_list as $key => $val) {
			fwrite($fh, $key." = ".$val."\n");
		}

		fwrite($fh, "end(SCANNER_SET)\n");

		// This defines the start of the plugin list
		fwrite($fh,"begin(PLUGIN_SET)\n");

		foreach($unique_list as $key => $val) {
			fwrite($fh, $key." = ".$val."\n");
		}

		fwrite($fh, "end(PLUGIN_SET)\n");

		flock($fh, LOCK_UN); // release the lock
	} else {
		define_syslog_variables();
		syslog(LOG_WARNING, "Couldn't lock the nessusrc file !");
	}

	fclose($fh);

	chmod($filename, 0777);
	return $filename;
}

function make_machine_list($plugin_id) {
	global $db,$sql;
	$list = array();

	$stmt = $db->prepare($sql['machine_list']);
	$stmt->execute($plugin_id);

	while($row = $stmt->fetch_assoc()) {
		$list[] = $row['machine'];
	}
	
	return $list;
}

function merge_all($plugin_id, &$scanner_set) {
	global $db,$sql;
	$all = false;

	$stmt = $db->prepare($sql['profile_plugins']);
	$stmt->execute($plugin_id);

	if ($stmt->num_rows() > 0) {
		while($row = $stmt->fetch_assoc()) {
			if ($row['plugin_type'] == 'all')
				$all = true;
		}

		if ($all) {
			foreach($scanner_set as $key => $val) {
				$scanner_set[$key] = 'yes';
			}
		}
	}
}

function merge_severities($plugin_id, &$scanner_set) {
	global $db, $sql;

	$severities = array();

	$stmt = $db->prepare($sql['profile_plugins']);
	$stmt->execute($plugin_id);

	if ($stmt->num_rows() > 0) {
		while($row = $stmt->fetch_assoc()) {
			if ($row['plugin_type'] == 'sev')
				$severities[] = $row['plugin'];
		}

		if(count($severities) > 0) {
			$stmt = $db->prepare($sql['plug_by_severity']);
			foreach($severities as $key => $sev) {
				$stmt->execute($sev);
				while($row = $stmt->fetch_assoc()) {
					$scanner_set[$row['pluginid']] = 'yes';
				}
			}
		}
	}
}

function merge_families($plugin_id, &$scanner_set) {
	global $db, $sql;

	$families = array();

	$stmt = $db->prepare($sql['profile_plugins']);
	$stmt->execute($plugin_id);

	if ($stmt->num_rows() > 0) {
		while($row = $stmt->fetch_assoc()) {
			if ($row['plugin_type'] == 'fam')
				$families[] = $row['plugin'];
		}

		if(count($families) > 0) {
			$stmt = $db->prepare($sql['plug_by_family']);
			foreach($families as $key => $fam) {
				$stmt->execute($fam);
				while($row = $stmt->fetch_assoc()) {
					$scanner_set[$row['pluginid']] = 'yes';
				}
			}
		}
	}
}

function merge_plugins($plugin_id, &$scanner_set) {
	global $db, $sql;

	$plugins = array();

	$stmt = $db->prepare($sql['profile_plugins']);
	$stmt->execute($plugin_id);

	if ($stmt->num_rows() > 0) {
		while($row = $stmt->fetch_assoc()) {
			if ($row['plugin_type'] == 'plu')
				$scanner_set[$row['plugin']] = 'yes';
		}
	}
}

?>
