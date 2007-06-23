<?php

// Full path to sudo followed by the full path to nmap
define('_NMAP_CMD', "/usr/bin/sudo /usr/bin/nmap");

/**
* Specify how portscan-me-now will determine and
* make use of javascript. Possible values are
*
* auto	portscan-me-now will automatically determine if javascript can be used
* on	force portscan-me-now to always output javascript reliant code
* off	force portscan-me-now to never output javascript reliant code
*/
define("_JAVASCRIPT", "auto");

// Version of scan-me-now
define("_VERSION", "1.0");

?>
