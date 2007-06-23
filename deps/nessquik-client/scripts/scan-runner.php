#!/usr/bin/php -q

<?php

set_time_limit(0);

/**
* Scan Runner will loop and fork all the scans that need to be run
*
* This script wraps around the scan-maker script. scan-maker will
* only create a single scan. In previous versions, scan-maker was
* not able to fork itself for each scan that needed to be run. This
* obviously created problems if lots of scans needed to be run. They
* were all run sequentially, and this could take a lot of time.
*
* scan-runner works around this by selecting a set of scans to run
* and then forking a copy of scan-maker for each scan. It passes it
* the profile ID to use for the scan. scan-maker will use that ID
* to perform the correct scan.
*/
if (!@$argc) {
	die ("<p>script can only be run from command line");
}


define("_ABSPATH", dirname(dirname(__FILE__)));

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/lib/functions.php');
require_once(_ABSPATH.'/lib/IXR_Library.php');

if (_DEBUG) {
	error_reporting(0);
}

$date = date('Y-m-d H:i:s');

$client = getIXRClient();

$client->query('jobs.getCountRunning', _CLIENT_KEY);
$scan_count = $client->getResponse();

// Check to see if too many scans are running
if ($scan_count >= _SCAN_LIMIT) {
	exit;
}

$client->query('jobs.getPendingProfileIds', _CLIENT_KEY, _SCAN_LIMIT);
$profiles = $client->getResponse();

if (count($profiles) == 0) {
	exit;
}

// Loop through each profile's information
foreach($profiles as $key => $profile_id) {
	$cmd = "nohup "._ABSPATH."/scripts/scan-maker.php $profile_id > /dev/null 2>&1 &";

	// Fork all the processes
	exec($cmd);
}

?>
