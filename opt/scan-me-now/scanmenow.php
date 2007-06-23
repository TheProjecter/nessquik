<?php

/**
* @author Joe Klemencic. Modified heavily by Tim Rupp
*/

define('_ABSPATH', dirname(__FILE__));
set_time_limit(0); 
define_syslog_variables();

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/lib/smarty/Smarty.class.php');
require_once(_ABSPATH.'/lib/Browscap.php');
require_once(_ABSPATH.'/lib/Nessus.php');

$logtype	= "Full Nessus Scan";
$tpl		= new Smarty;
$javascript	= false;
$badchars 	= array('~','!','@','#','$',
	'%','^','&','*','(',')',
	'_','+','`','=',';','\'',
	'"',',','<','>','?','{',
	'}','[',']','|','\\'
);

if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else { 
	$client_ip = $_SERVER["REMOTE_ADDR"];
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

$client_ip	= trim(str_replace($badchars,"", $client_ip));
$uniq		= strtotime("now") . '-' .mt_rand(0,100);

$scan_file	= _ABSPATH . "/scans/scanmenow-".$client_ip.'-'.$uniq;
$log_file	= _ABSPATH . "/logs/scanmenow-".$client_ip.'-'.$uniq.'.log';

$tpl->assign('client_ip', $client_ip);

if ($javascript) {
	$tpl->display('processing.tpl');
} else {
	$tpl->display('processing_no_js.tpl');
}

@ob_end_flush();
flush();

@$fp_scan	= fopen($scan_file, 'w');
@$fp_log 	= fopen($log_file, 'w');

if (!is_resource($fp_log)) {
	$error = "Error creating scan-me-now log file";

	if ($javascript) {
		echo "<script type='text/javascript'>"
		. "document.getElementById('processing_steps').innerHTML = \"<span style='color: #000;'>"
		. "<center>$error</center><br>"
		. "</span>\";\n"
		. "</script>";
	} else {
		echo $error;
	}

	syslog(LOG_ERR, $error);
	flush();
	exit;
} else {
	fwrite($fp_log, "scan-me-now opened log file at " . strftime("%m-%d-%Y %I:%M:%S %p", time()) . "\n");
}

if (!is_resource($fp_scan)) {
	$error = "Error creating host specification file";

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
	fwrite($fp_scan, $client_ip);
	fwrite($fp_log, "Scanning: $client_ip\n");

	// Close the scan file so it can be read by Nessus.
	// Keep the log file open though
	fclose($fp_scan);
}

$nessus_plug	= "nohup "._NESSUS_CMD." -p -x -q "._NESSUS_SERVER.' '._NESSUS_PORT.' '._NESSUS_USER.' '._NESSUS_PASS."|/usr/bin/wc -l";
$nessus_cmd	= "nohup "._NESSUS_CMD." -c "._NESSUS_CONFIG." -T nbe -x -V -q "._NESSUS_SERVER.' '._NESSUS_PORT.' '._NESSUS_USER.' '._NESSUS_PASS." $scan_file -";

fwrite($fp_log, "Running Commands:\n");
fwrite($fp_log, "\t$nessus_plug\n");
fwrite($fp_log, "\t$nessus_cmd\n");

$numplugs	= `$nessus_plug`;
$prognumplugs	= $numplugs + 1;
$reading_output	= false;

$time		= date("m-d-Y-H:i:s", time());

syslog(LOG_INFO, "ScanMeNow: Starting $logtype on $client_ip at $time");
fwrite($fp_log, "ScanMeNow: Starting $logtype on $client_ip at $time\n");

$handle = popen($nessus_cmd, 'r');

while(!feof($handle)) {
	$line = fgets($handle);
	fwrite($fp_log, $line."\n");

	if (strpos($line, "No such file") !== false) {
		$error = "Unable to connect to the Nessus server<br>";

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

		unlink($scan_file);

		@ob_end_flush();
		flush();
		exit;
	}

	if (!$reading_output) {
		if (strpos($line, "timestamps") !== false) {
			$reading_output = true;
			$output = str_replace('"',"'",$line).':::';

			continue;
		}

		if(stristr($line,"attack|")) {
			$tmp = explode("|",$line);
			$aplug = $tmp[2];

			if ($javascript) {
				echo "<script type='text/javascript'>"
				. "document.getElementById('processing_steps').innerHTML = \"<span style='color: #000;'>"
				. "<div class='percentbox2'>Running test $aplug out of a possible $prognumplugs</div>"
				. "</span>\";\n"
				. "</script>";
			} else {
				echo "<div class='percentbox' style='z-index:0; background-image(\"../images/white.png\"); height: 40px;'>"
				. "&nbsp;"
				. "</div>";

				@ob_end_flush();
				flush();

				echo "<div class='percentbox' style='z-index:0;'>"
				. "Running test $aplug out of a possible $prognumplugs"
				. "</div>\n";
			}
		}
		@ob_end_flush();
		flush();
	} else {
		$output .= str_replace('"',"'",$line).':::';
	}
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

$_nes = new Nessus($output);
$output = trim($_nes->output_html('', true, true));

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
syslog(LOG_INFO, "ScanMeNow: Finished $logtype on $client_ip at $time");
fwrite($fp_log, "ScanMeNow: Finished $logtype on $client_ip at $time\n");

fclose($fp_log);
unlink($scan_file);

?>
