<?php

/**
* @author Joe Klemencic. Moddified heavily by Tim Rupp
*/
define('_ABSPATH', dirname(__FILE__));
set_time_limit(0); 
define_syslog_variables();

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/lib/smarty/Smarty.class.php');
require_once(_ABSPATH.'/lib/Browscap.php');
require_once(_ABSPATH.'/lib/functions.php');
#require_once(_ABSPATH.'/lib/Nmap.php');

$logtype	= "Full Nessus Scan";
$tpl		= new Smarty;
$javascript	= false;
$uniq		= strtotime("now") . '-' .mt_rand(0,100);
$output		= '';

if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$client_ip = import_var('HTTP_X_FORWARDED_FOR', 'SE');
} else { 
	$client_ip = import_var('REMOTE_ADDR', 'SE');
}

if ($client_ip == false) {
	die("Client IP not valid");
}

switch(_JAVASCRIPT) {
	default:
	case "auto":
		$bc = new Browscap;
		$current_browser = $bc->getBrowser();

		// Output the result
		if ($current_browser->JavaScript == 1) {
			$javascript = true;
		} else {
			$javascript = false;
		}
		break;
	case "on":
		$javascript = true;
		break;
	case "off":
		$javascript = false;
		break;
}


$log_file	= _ABSPATH . "/logs/portscanmenow-".$client_ip.'-'.$uniq.'.log';
$scantype	= import_var('SCANTYPE', 'G');
$verbose	= import_var('VERBOSE', 'G');
$port		= import_var('PORT', 'G');

$port 		= get_port($port);
$verbose	= get_verbose($verbose);

if($scantype == "A") {
	if ($port != "1-65535") {
		$hdrtext	= "Performing Aggressive Port $port Nmap Port Scan";
		$logtype	= "Port $port Aggressive Nmap Scan";
	} else {
		$hdrtext	= "Performing Aggressive 65k Nmap Port Scan";
		$logtype	= "Aggressive 65k Nmap Scan";
	}

	$scanlength	= "1 minute";
	$options	= "-sS -p $port -A -P0 -T4 --osscan_limit --osscan_guess --host_timeout 40m --max-retries 0";
} else {
	if ($port != "1-65535") {
		$hdrtext	= "Performing Port $port Nmap Port Scan";
		$logtype	= "Port $port Nmap Scan";
	} else {
		$hdrtext	= "Performing 65k Nmap Port Scan";
		$logtype	= "65k Nmap Scan";
	}

	$scanlength	= "1 minute";
	$options	= "-sS -p $port -P0 -T4 --osscan_limit --osscan_guess --host_timeout 40m --max-retries 0";
}

$tpl->assign(array(
	'client_ip'	=> $client_ip,
	'hdrtext'	=> $hdrtext,
));

if ($javascript) {
	$tpl->display('processing.tpl');
} else {
	$tpl->display('processing_no_js.tpl');
}

@ob_end_flush();
flush();

@$fp_log 	= fopen($log_file, 'w');

if (!is_resource($fp_log)) {
	$error = "Error creating portscan-me-now log file";

	if ($javascript) {
		echo "<script type='text/javascript'>"
		. "document.getElementById('processing_steps').innerHTML = \"<span style='color: #000;'>"
		. "<center>$error</center><br>"
		. "</span>\";\n"
		. "</script>";
	} else {
		echo $error."<br>";
	}

	syslog(LOG_ERR, $error);
	flush();
	exit;
} else {
	fwrite($fp_log, "portscan-me-now opened log file at " . strftime("%m-%d-%Y %I:%M:%S %p", time()) . "\n");
}

$nmap_cmd	= "nohup "._NMAP_CMD." $options $verbose $client_ip";

fwrite($fp_log, "Running Commands:\n");
fwrite($fp_log, "\t$nmap_cmd\n");

$reading_output	= false;
$time		= date("m-d-Y-H:i:s", time());

syslog(LOG_INFO, "PortScanMeNow: Starting $logtype on $client_ip at $time");
fwrite($fp_log, "PortScanMeNow: Starting $logtype on $client_ip at $time\n");

$handle = popen($nmap_cmd, 'r');

if ($javascript) {
	echo "<script type='text/javascript'>"
	. "document.getElementById('processing_steps').innerHTML = \"<center>"
	. "<img src='images/spinner.gif'></center>"
	. "</span>\";\n"
	. "</script>";
} else {
	echo "<div class='percentbox' style='z-index: 0; background-image(\"../images/white.png\"); height: 40px;'>"
	. "&nbsp;"
	. "</div>";

	@ob_end_flush();
	flush();

	echo "<div class='percentbox' style='z-index: 0; height: 40px;'>"
	. "<img src='images/spinner.gif'>"
	. "</div>";
}

@ob_end_flush();
flush();

while(!feof($handle)) {
	$line = fgets($handle);
	fwrite($fp_log, $line."\n");

	if (strpos($line, "command not found") !== false) {
		$error = "Nmap doesn't appear to be installed<br>";

		if ($javascript) {
			echo "<script type='text/javascript'>"
			. "document.getElementById('processing_steps').innerHTML = \"<span style='color: #000;'>"
			. "<center>$error</center>"
			. "</span>\";\n"
			. "</script>";
		} else {
			echo $error;
		}

		syslog(LOG_ERR, $error);
		fwrite($fp_log, $error."\n");

		@ob_end_flush();
		flush();
		exit;
	}

	/**
	* Replace this line with
	*
	*	$output .= $line;
	*
	* for 2.6 and XML
	*/
	$output .= str_replace('"',"'",$line."<p>");
}

pclose($handle);

$read = "Scan of $client_ip Complete!";
fwrite($fp_log, $read."\n");

if ($javascript) {
	echo "<script type='text/javascript'>"
	. "document.getElementById('processing_steps').innerHTML = \"<span style='color: #000;'>"
	. "<div class='percentbox2'>$read</div><br><br>"
	. "</span>\";\n"
	. "</script>";
} else {
	echo "<div class='percentbox' style='z-index: 0; background-image(\"../images/white.png\"); height: 40px;'>"
	. "&nbsp;"
	. "</div>";

	@ob_end_flush();
	flush();

	echo "<div class='percentbox' style='z-index: 0;'>$read</div>";
}

@ob_end_flush();
flush();

/**
* Reserved for 2.6 and XML
*
$_nmp = new Nmap($output);
$_nmp->parse($output);
*/

// Must replace newlines so javascript wont break
$output = str_replace("\n", "", $output);

if ($javascript) {
	echo "<script type='text/javascript'>"
	. "document.getElementById('processing_output').innerHTML = \"<span style='color: #000;'>$output</span>\";\n"
	. "</script>";
} else {
	echo "</div><br><br><br><br>";
	echo $output;
	echo "</body></html>";
}

@ob_end_flush();
flush();

$time = date("m-d-Y-H:i:s", time());
syslog(LOG_INFO, "PortScanMeNow: Finished $logtype on $client_ip at $time");
fwrite($fp_log, "PortScanMeNow: Finished $logtype on $client_ip at $time\n");

fclose($fp_log);

?>
