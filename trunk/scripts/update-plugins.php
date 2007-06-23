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

$cmd = _NESSUS_CMD . " -p -x -q " . _NESSUS_SERVER . ' ' . _NESSUS_PORT . ' ' . _NESSUS_USER . ' ' . _NESSUS_PASS;

$handle = popen($cmd, 'r') or die ("Path to Nessus is incorrect");

while(!feof($handle)) {
	// Get a line of data
	$line = fgets($handle, 4096);

	// split it up by the pipe character
	$data = explode('|', $line);

	// Plugin ID will not be updated
	$pluginid	= $data[0];

	// All the remaining data will have whitespace trimmed
	@$family	= trim($data[1]);
	@$kb		= trim($data[2]);
	@$severity	= trim($data[3]);
	@$copyright	= trim($data[4]);
	@$shortdesc	= trim($data[5]);

	// If there's no description, then there is no plugin. Pass
	if ($shortdesc == '') {
		continue;
	}

	$rev		= trim($data[6]);
	$cve		= trim($data[7]);
	$bugtraq1	= trim($data[8]);
	$bugtraq2	= trim($data[9]);

	// Replace new lines with breaks because newlines wont display correctly in HTML
	$desc		= trim(str_replace('\n','<br>&nbsp;',$data[10]));

	$sql = array(
		'insert' => "	INSERT INTO plugins (`pluginid`,
					`family`,
					`kb`,
					`sev`,
					`copyright`,
					`shortdesc`,
					`rev`,
					`cve`,
					`bugtraq1`,
					`bugtraq2`,
					`desc`) 
				VALUES (':1',
					':2',
					':3',
					':4',
					':5',
					':6',
					':7',
					':8',
					':9',
					':10',
					':11') 
				ON DUPLICATE KEY UPDATE family=':12',
					kb=':13',
					sev=':14',
					copyright=':15',
					shortdesc=':16',
					rev=':17',
					cve=':18',
					bugtraq1=':19',
					bugtraq2=':20',
					`desc`=':21';"
	);

	$stmt1 = $db->prepare($sql['insert']);

	/**
	* First batch of variables is for the insert and the second batch
	* is for the update. I need to fix my database layer to better handle
	* multiple level inserts. Once that is done, about half of this function
	* call can be eliminated.
	*/
	$stmt1->execute(
		$pluginid,
		$family,
		$kb,
		$severity,
		$copyright,
		$shortdesc,
		$rev,
		$cve,
		$bugtraq1,
		$bugtraq2,
		$desc,

		$family,
		$kb,
		$severity,
		$copyright,
		$shortdesc,
		$rev,
		$cve,
		$bugtraq1,
		$bugtraq2,
		$desc
	);
}

echo "done\n";

?>
