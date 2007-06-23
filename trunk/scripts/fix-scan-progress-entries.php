#!/usr/bin/php

<?php

/**
* This script will fix the scan_progress table
* and replace any missing scan progress bars.
* Missing is defined as a scan profile listed
* in the profile_list table that doesn't have
* a progress bar entry in the scan_progress table.
*/

define('_ABSPATH', dirname(dirname(__FILE__)));

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/db/nessquikDB.php');

$db 		= nessquikDB::getInstance();
$profiles	= array();
$status		= array();

$sql = array(
	'profiles' => "	SELECT 	profile_id FROM profile_list;",

	'status' => "SELECT scan_id,profile_id FROM scan_progress",

	'delete' => "DELETE FROM scan_progress WHERE scan_id=':1' AND profile_id=':2'",

	'insert' => "	INSERT INTO scan_progress (
				`scan_id`,
				`profile_id`,
				`portscan_percent`,
				`attack_percent`
			) VALUES (NULL,':1',':2',':3');"
);

$stmt1 = $db->prepare($sql['profiles']);
$stmt2 = $db->prepare($sql['status']);
$stmt3 = $db->prepare($sql['insert']);
$stmt4 = $db->prepare($sql['delete']);

$stmt1->execute();
$stmt2->execute();

while($row = $stmt1->fetch_assoc()) {
	$profiles[] = $row['profile_id'];
}

while($row = $stmt2->fetch_assoc()) {
	$status[$row['scan_id']] = $row['profile_id'];
}


$to_add 	= array_diff($profiles, $status);
$to_remove	= array_diff($status, $profiles);

foreach($to_add as $key => $profile_id) {
	$stmt3->execute($profile_id, 0, 0);
}

foreach($to_remove as $scan_id => $profile_id) {
	$stmt4->execute($scan_id, $profile_id);
}

echo "\ndone\n";
