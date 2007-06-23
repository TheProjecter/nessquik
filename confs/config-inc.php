<?php

// Needed for all web->database operations
define("_DBUSER", "root");
define("_DBPASS", "password");
define("_DBSERVER", "localhost");
define("_DBPORT", 3306);
define("_DBUSE", "nessquik");
define("_CONNECT_TYPE", "");

// Used as a means of linking to plugins
$links = array(
	'nessus' 	=> "http://www.nessus.org/plugins/index.php?view=single&id=",
);

// Needed for updating the plugins table
define("_NESSUS_SERVER", "localhost");
define("_NESSUS_PORT", 1241);
define("_NESSUS_USER", "nessus");
define("_NESSUS_PASS", "password");
define("_NESSUS_CMD", "/usr/bin/sudo /usr/local/bin/nessus");

// Used to update the nasl_names table
define("_NESSUS_PLUG_DIR", "/usr/local/lib/nessus/plugins/");

// Used when making the nessusrc file. If you dont have one then leave it blank
define("_TRUSTED_CA", "/usr/local/com/nessus/CA/cacert.pem");

// Used for including files
if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(__FILE__)));
}

// Mail server settings
define("_SMTP_SERVER", "localhost");
define("_SMTP_AUTH", false);
define("_SMTP_FROM", "nessquik@localhost");
define("_SMTP_FROM_NAME", "nessquik");

// Version of nessquik
define("_VERSION", "2.5");

// Turn debugging on or off. This affects the errors that are displayed
define("_DEBUG", false);

// Release of nessquik
define("_RELEASE", "general");

// Define session timeout value in seconds
define("_TIMEOUT", 86400);

// Whether or not nessquik should check to make sure it is being run over HTTPS
define("_CHECK_SECURE", true);

// Primary email will be sent to this email address
define("_RECIPIENT", "root@localhost");

// Needed to support the packaged PEAR
ini_set("include_path", ".:"._ABSPATH."/lib/pear");

$allowed_editors = array(
	'admin'
);

?>
