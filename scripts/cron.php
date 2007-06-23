#!/usr/bin/php -q

<?php

set_time_limit(0);

if (!@$argc) {
	die ("<p>script can only be run from command line");
}

#error_reporting(0);

define("_ABSPATH", dirname(dirname(__FILE__)));

require_once(_ABSPATH.'/lib/Cron.php');

define_syslog_variables();

$cron = new Cron;
$cron->run();

?>
