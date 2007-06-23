#!/usr/bin/php -q

<?php

set_time_limit(0);

if (!@$argc) {
	die ("<p>This script can only be run from command line");
}

define('_ABSPATH', dirname(dirname(__FILE__)));

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/db/nessquikDB.php');

$db 	= nessquikDB::getInstance();
$nasl	= array();

$sql = array(
	'insert' => "	INSERT INTO nasl_names (`pluginid`,
				`script_name`) 
			VALUES (':1',':2') 
			ON DUPLICATE KEY UPDATE script_name=':3'"
);
$stmt = $db->prepare($sql['insert']);

// If the path sent is a directory...
if (is_dir(_NESSUS_PLUG_DIR)) {
	// ...check to see if we can open it. If yes, store its resource in a variable
	if ($handle = opendir(_NESSUS_PLUG_DIR)) {
		// If we're capable of opening the directory, reading files in one at a time until no more
		while (false !== ($file = readdir($handle))) {
			// Check to see if the filename is either the current dir, or the parent dir.
			// and skip it if it is.
			if ($file != "." && $file != "..") {
				$nasl[] = $file;
			}
		}
	}
}

// Close the directory we were working with
closedir($handle);

/**
* Slow and tedious process of getting the plugin id from the nasl
* file and taking it and the file name and pushing them into the
* database.
*/
$filename 	= '';
$fullpath 	= '';
$plugin_pairs	= array();
$pattern	= '/[0-9]+/';

foreach($nasl as $key => $filename) {
	$fullpath = _NESSUS_PLUG_DIR . '/' . $filename;

	$fh = fopen($fullpath, 'r');

	while(!feof($fh)) {
		$matches = array();

		$line = fgets($fh,4096);

		$line = trim($line);

		if(strpos($line, "script_id(") === false)
			continue;
		else {
			preg_match($pattern,$line,$matches);
			if (count($matches) > 0) {
				$plugin_pairs[$matches[0]] = $filename;
				break;
			}
		}
	}

	fclose($fh);
}

/**
* By now we have an array that is indexed by plugin id with
* the value of each item being the file name of the nasl script.
* Now all that is left is to insert the data into the table.
*/
$id		= 0;
$script_name	= '';

foreach ($plugin_pairs as $id => $script_name) {
	$stmt->execute($id,$script_name,$script_name);
}

?>
