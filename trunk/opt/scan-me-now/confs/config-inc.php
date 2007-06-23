<?php

// Needed for updating the plugins table
define("_NESSUS_SERVER", "localhost");
define("_NESSUS_PORT", 1241);
define("_NESSUS_USER", "nessus");
define("_NESSUS_PASS", "password");
define("_NESSUS_CMD", "/usr/bin/sudo /usr/local/bin/nessus");
define("_NESSUS_CONFIG", "/var/www/html/scan-me-now/confs/scanmenow.rc");

/**
* Specify how scan-me-now will determine and
* make use of javascript. Possible values are
*
* auto	scan-me-now will automatically determine if javascript can be used
* on	force scan-me-now to always output javascript reliant code
* off	force scan-me-now to never output javascript reliant code
*/
define("_JAVASCRIPT", "auto");

// Version of scan-me-now
define("_VERSION", "1.0");

?>
