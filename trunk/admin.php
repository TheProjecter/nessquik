<?php

session_name('nessquik');
session_start();

try {
	require_once('confs/config-inc.php');
} catch(Exception $e) {
	die("No config file was found");
}
require_once(_ABSPATH.'/lib/Smarty.php');
require_once(_ABSPATH.'/lib/functions.php');
require_once(_ABSPATH.'/db/nessquikDB.php');
require_once(_ABSPATH.'/lib/User.php');
require_once(_ABSPATH.'/lib/SysOps.php');

$_sys = SysOps::getInstance();
$_sys->check_cache_writable(true);

if (_RELEASE == "fermi") {
	// Session authentication code using certificates
	$logout			= false;
	$timed_out 		= time() - _TIMEOUT;
	$agent			= import_var('HTTP_USER_AGENT', 'SE');
	$time			= import_var('login', 'S');
	$client_dn		= import_var('client_dn', 'S');

	$check_fingerprint	= md5($agent.$time);
	$real_fingerprint	= import_var('fingerprint', 'S');

	/**
	* If the user suddenly is using a different browser,
	* flag that as suspicious and log them out.
	*/ 
	if ($check_fingerprint != $real_fingerprint) {
		$logout = true;
	}

	/**
	* If the session has timed out, also log them out.
	*/
	if ($time < $timed_out) {
		$logout = true;
	}

	/**
	* If somehow the session code never saved a client_dn
	* then badness could happen. Therefore redirect them
	* to logout and try resetting the dn
	*/
	if ($client_dn == '') {
		$logout = true;
	}

	// Send them away to log out and log back in again if neccessary
	if ($logout) {
		header("Location: deps/nessquik-main/logout.php");
	}
}

$db 	= nessquikDB::getInstance();
$tpl	= SmartyTemplate::getInstance();
$usr	= User::getInstance();
$chk	= SysOps::getInstance();

$chk->check_version();

$username	= import_var('username', 'S');
$page		= import_var('page', 'G');
$editor         = 0;

if ($chk->check_secure()) {
	$tpl->assign('check_secure', true);
} else {
	$tpl->assign('check_secure', false);
}

$editor		= $usr->is_editor($allowed_editors);

if(!$editor) {
        $tpl->assign('MESSAGE', "<center>You do not have permission to access this page.</center>");
        $tpl->assign('RETURN_LINK', "<center><p><a href='index.php'>Return to the main page</a></p></center>");
	$tpl->assign('SUCCESS', 'noper');
        $tpl->display('actions_done.tpl');
        exit;
}

$_SESSION['admin'] = "1";

$tpl->assign('check_nessus', $chk->check_nessus());
$tpl->assign('username', $username);
$tpl->assign('version', _VERSION);
$tpl->assign('_RELEASE', _RELEASE);

switch($page) {
	case "accounts":
		$tpl->assign('page', 'accounts');
		break;
	case "scans":
		$tpl->assign('page', 'scans');
		break;
	case "help":
		$tpl->assign('page', 'help');
		break;
	case "settings":
		$tpl->assign('page', 'settings');
		break;
	case "metrics":
		require_once(_ABSPATH.'/lib/MetricsInstaller.php');

		$graph	= new MetricsInstaller(_ABSPATH.'/lib/metrics/graphs', "graphs");
		$report	= new MetricsInstaller(_ABSPATH.'/lib/metrics/reports', "reports");

		$graph->discover_metrics();
		$report->discover_metrics();

		if ($graph->new_metrics || $report->new_metrics) {
			$tpl->assign("new_metrics", true);
		} else {
			$tpl->assign("new_metrics", false);
		}

		$time = time();

		$tpl->assign(array(
			'page'			=> 'metrics',
			'month'			=> strftime('%B', $time),
			'day'			=> strftime('%d', $time),
			'start_year'		=> (strftime('%Y', $time) - 1),
			'end_year'		=> strftime('%Y', $time),
			'hour'			=> strftime('%I', $time),
			'minute'		=> strftime('%M', $time),
			'ampm'			=> strftime('%p', $time),
			'month_input'		=> strftime('%Y-%m-%d %I:%M %p', $time),
		));
		break;
	case "wlist":
		$sql = array(
			'select' => "SELECT DISTINCT(username) FROM whitelist ORDER BY username ASC;"
		);
		$count 	= 0;
		$users	= array();

		$stmt = $db->prepare($sql['select']);
		$stmt->execute();

		$data = "<table width='100%'>";

		while ($row = $stmt->fetch_assoc()) {
			$users[] = array(
				'username' => $row['username']
			);
		}

		$tpl->assign('users',$users);
		$tpl->assign('page', 'wlist');
		break;
	default:
		$tpl->assign('page', 'admin');
		break;
}

$tpl->display('admin.tpl');

?>
