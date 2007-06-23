<?php

// Server name of the nessquik server
define('_NESSQUIK_SRVR', 'localhost');

// Pat to the nessquik install on the nessquik server
define('_NESSQUIK_PATH', '/nessquik/');

// Port used to talk to the nessquik server
define('_NESSQUIK_PORT', 443);

// Your client's unique ID. Generated at time of scanner creation
define('_CLIENT_KEY', 'oILADzZaMIbryc4whlx23saGywdNSHry');

/**
* For users who wish to use "throwaway" SSL communication,
* set _NESSQUIK_SSL to true, but leave the remaining define
* empty.
*
* "Throwaway" communication is what you'd use if, for example,
* you have a self signed certificate, and you don't pass along
* all the CA certs to keep your browser from poping up those
* nag messages that are along the lines of "this cert is untrusted"
* blah blah blah.
*
* If on the other hand, you _do_ distribute the files needed
* below, then you'd want to define them to ensure your SSL client
* is talking to a legit server.
*/
define('_NESSQUIK_SSL', true);

	// Client public key (ex. myPublicCert.pem)
	define('_NESSQUIK_PUB_CERT', '');

	// Client private key (ex. myPrivateKey.pem)
	define('_NESSQUIK_PRIV_KEY', '');

	// Password/phrase for the private key
	define('_NESSQUIK_SEC_PASS', '');

	// Location of the CA certificate (to validate the server's certificate)
	define('_NESSQUIK_CA_CERT', '');

/**
* If you're using HTTP based authentication in an htaccess
* file, you'll want to set this to true and fill in
* the username and password that the nessquik client should
* use to reach the nessquik installation.
*
* Be aware that this feature may be deprecated in the future.
* It was made available after I realized that I overlooked a
* critical step in the installation of nessquik by telling
* everyone to htaccess their install. When user accounts are
* available, this feature probably will become irrelevant and
* you can remove it at that time if you choose to do away with
* the htaccess file
*/
define('_HTTP_AUTH', false);

	// Username for HTTP authentication
	define('_HTTP_AUTH_USER', "username");

	// Password for the username above
	define('_HTTP_AUTH_PASS', "password");

// Needed for running the Nessus scan
define("_NESSUS_SERVER", "localhost");
define("_NESSUS_PORT", 1241);
define("_NESSUS_USER", "nessus");
define("_NESSUS_PASS", "mypassword");
define("_NESSUS_CMD", "/usr/bin/sudo /usr/local/bin/nessus");
define("_NESSUS_STORE", "/opt/nessquik-client/scans/");

// Used when making the nessusrc file. If you dont have one then leave it blank
define("_TRUSTED_CA", "/usr/local/com/nessus/CA/cacert.pem");

// Used for including files
if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(__FILE__)));
}

// Version of nessquik client
define("_VERSION", "2.5");

// For troubleshooting, you may be asked to set this to true
define("_DEBUG", false);

// Release of nessquik
define("_RELEASE", "general");

// Max number of scans to fork
define("_SCAN_LIMIT", 20);

?>
