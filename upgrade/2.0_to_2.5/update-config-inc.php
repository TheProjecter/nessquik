<?php

if (!@$argc) {
	die ("<p>This script can only be run from command line");
}

define('_NEW_ABSPATH', dirname(dirname(dirname(__FILE__))));

$old_config_file = $argv[1];

if (is_writable(_NEW_ABSPATH.'/confs/')) {
	$new_config_file = fopen(_NEW_ABSPATH.'/confs/config-inc.php', 'w');
} else {
	die ("You don't have permission to write to the new confs directory\n\n\t"._NEW_ABSPATH."/confs/\n");
}

if ($old_config_file == '') {
	die ("You must specify an old configuration file to update\n");
} else {
	require($old_config_file);
}


/**
* Write the new configuration file line by line
*/

fwrite($fh, "<?php\n");
fwrite($fh, "\n");
fwrite($fh, "// Needed for all web->database operations\n");
fwrite($fh, 'define("_DBUSER", "' . _DBUSER . '");' . "\n");
fwrite($fh, 'define("_DBPASS", "' . _DBPASS . '");' . "\n");
fwrite($fh, 'define("_DBSERVER", "' . _DBSERVER . '");' . "\n");
fwrite($fh, 'define("_DBPORT", ' . _DBPORT . ');' . "\n");
fwrite($fh, 'define("_DBUSE", "' . _DBUSE . '");' . "\n");
fwrite($fh, 'define("_CONNECT_TYPE", "");' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Used as a means of linking to plugins\n");
fwrite($fh, '$links = array(' . "\n");
fwrite($fh, "\t'nessus'\t\t=> " . '"http://www.nessus.org/plugins/index.php?view=single&id=",' . "\n");
fwrite($fh, ");");
fwrite($fh, "\n");
fwrite($fh, "// Needed for updating the plugins table\n");
fwrite($fh, 'define("_NESSUS_SERVER", "' . _NESSUS_SERVER . '");' . "\n");
fwrite($fh, 'define("_NESSUS_PORT", ' . _NESSUS_PORT . ');' . "\n");
fwrite($fh, 'define("_NESSUS_USER", "' . _NESSUS_USER . '");' . "\n");
fwrite($fh, 'define("_NESSUS_PASS", "' . _NESSUS_PASS . '");' . "\n");
fwrite($fh, 'define("_NESSUS_CMD", "' . _NESSUS_CMD . '");' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Used to update the nasl_names table\n");
fwrite($fh, 'define("_NESSUS_PLUG_DIR", "' . _NESSUS_PLUG_DIR . '");' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Used when making the nessusrc file. If you dont have one then leave it blank\n");
fwrite($fh, 'define("_TRUSTED_CA", "' . _TRUSTED_CA . '");' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Used for including files\n");
fwrite($fh, 'if (!defined("_ABSPATH")) {' . "\n");
fwrite($fh, "\t" . 'define("_ABSPATH", dirname(dirname(__FILE__)));' . "\n");
fwrite($fh, "}\n");
fwrite($fh, "\n");
fwrite($fh, "// Mail server settings\n");
fwrite($fh, 'define("_SMTP_SERVER", "' . _SMTP_SERVER . '");' . "\n");
fwrite($fh, 'define("_SMTP_AUTH", ' . _SMTP_AUTH . ');' . "\n");
fwrite($fh, 'define("_SMTP_FROM", "' . _SMTP_FROM . '");' . "\n");
fwrite($fh, 'define("_SMTP_FROM_NAME", "' . _SMTP_FROM_NAME . '");' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Version of nessquik\n");
fwrite($fh, 'define("_VERSION", "2.5");' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Turn debugging on or off. This affects the errors that are displayed\n");
fwrite($fh, 'define("_DEBUG", false);' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Release of nessquik\n");
fwrite($fh, 'define("_RELEASE", "general");' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Define session timeout value in seconds\n");
fwrite($fh, 'define("_TIMEOUT", ' . _TIMEOUT . ');' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Whether or not nessquik should check to make sure it is being run over HTTPS\n");
fwrite($fh, 'define("_CHECK_SECURE", true);' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Primary email will be sent to this email address\n");
fwrite($fh, 'define("_RECIPIENT", "' . _RECIPIENT . '");' . "\n");
fwrite($fh, "\n");
fwrite($fh, "// Needed to support the packaged PEAR\n");
fwrite($fh, 'ini_set("include_path", ".:"._ABSPATH."/lib/pear");' . "\n");
fwrite($fh, "\n");
fwrite($fh, '$allowed_editors = array(' . "\n");
fwrite($fh, "\t'admin'\n");
fwrite($fh, ");");
fwrite($fh, "\n");
fwrite($fh, '?>');

echo "Config file updated\n\n";

?>
