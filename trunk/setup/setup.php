#!/usr/bin/php -q

<?php

set_time_limit(0);

if (!@$argc) {
	die ("<p>script can only be run from command line");
}

define('_ABSPATH', dirname(dirname(__FILE__)));

if (file_exists(_ABSPATH.'/confs/config-inc.php')) {
	require_once(_ABSPATH.'/confs/config-inc.php');
	require_once(_ABSPATH.'/db/nessquikDB.php');
} else {
	die("The config.inc.php file was not found\n\n");
}

if (_DBUSER == "root" && _DBPASS == "password") {
	die("Please change the values in the config-inc.php file "
	. "to match your environment, before you run the setup script.\n\n");
}

$db 	= nessquikDB::getInstance();

$sql = array(
	'all_groups' => "INSERT INTO division_group_list(`group_name`) VALUES (':1');"
);

$stmt01 = $db->prepare($sql['all_groups']);

// Creates all the tables in the nessquik database
$db->load_sql_file(_ABSPATH."/setup/sql/structure.sql");

// Creates the All Groups group
$stmt01->execute("All Groups");

// Insert all the help topics and categories
$db->load_sql_file(_ABSPATH."/setup/sql/help.sql");
$db->load_sql_file(_ABSPATH."/setup/sql/help-categories.sql");

echo "Database setup done.\n\n";
echo "There are a couple more things you need to do\n";
echo "\t - Change the ownership of the templates_c directory to be owned by the Apache user\n";
echo "\t - Run the update-plugins.php script in scripts/";
echo "\t - Run the nasl_name_updater.php script in scripts/";

?>
