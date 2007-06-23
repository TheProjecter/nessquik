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

if (_RELEASE == "fermi" ) {
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
} else {
	$_SESSION['username'] = "admin";
}

$db 	= nessquikDB::getInstance();
$tpl	= SmartyTemplate::getInstance();
$_usr	= User::getInstance();
$_chk	= SysOps::getInstance();

$_chk->check_version();

$sql = array(
	'select' => "	SELECT pluginid 
			FROM plugins 
			LIMIT 2",

	'settings' => "	INSERT INTO profile_settings(
				`username`,
				`profile_id`,
				`alternative_cgibin_list`) 
			VALUES (':1',':2',':3');",

	'select_settings' => "	SELECT profile_id 
				FROM profile_settings 
				WHERE username=':1' 
				AND setting_type='sys';"
);

$username 		= import_var('username', 'S');
$division		= $_usr->get_division_from_uid($username);
$division_id		= $_usr->get_division_id($division);
$editor			= false;
$has_whitelist		= false;
$has_saved_scans	= false;
$has_special_plugins	= false;
$has_clusters		= false;
$has_registered		= false;

$stmt 		= $db->prepare($sql['select']);
$stmt3 		= $db->prepare($sql['select_settings']);
$stmt4 		= $db->prepare($sql['settings']);

$proper 	= $_usr->get_proper_name($username);
$page		= import_var('page', 'G');
$vhosts		= $_usr->get_vhosts($username);
$editor		= $_usr->is_editor($allowed_editors);

// Execute SQL to check if plugins have been updated
$stmt->execute();

// Check to see if the user has a default scan profile yet
$stmt3->execute($username);

// Check to see if the username exists in the settings table
if ($stmt3->num_rows() < 1) {
	$profile_id = md5(mt_rand(0,1000000) + time());

	/**
	* If not, then create it. MySQL is stupid because you
	* cant specify a default value for text fields.
	*
	* Note this is the user's default profile that is made here
	*/
	$stmt4->execute($username, $profile_id, '/cgi-bin:/scripts');
} else {
	$profile_id = $stmt3->result(0);
}

$has_whitelist 		= ($_usr->has_whitelist($username)) 		? true : false;
$has_saved_scans	= ($_usr->has_saved_scans($username))		? true : false;
$has_special_plugins	= ($_usr->has_special_plugins($division_id)) 	? true : false;
$has_clusters		= ($_usr->has_clusters($username)) 		? true : false;
$has_registered		= ($_usr->has_registered($username)) 		? true : false;

// If plugins have not been updated, then die
if ($stmt->num_rows() < 1) {
	die ("You need to run the update-plugins and nasl_name_updater first");
}

if ($page == "settings") {
	$scanners_count = $_usr->count_available_scanners($division_id);

	$tpl->assign('scanners_count', $scanners_count);
	$tpl->assign('page', 'settings');
} else if ($page == "scans") {
	$tpl->assign('page', 'scans');
} else if ($page == "help") {
	$tpl->assign('page', 'help');
} else {
	$scanners_count = $_usr->count_available_scanners($division_id);

	$tpl->assign('scanners_count', $scanners_count);
	$tpl->assign('page', 'create');
}

$tpl->assign(array(
	'the_page'		=> import_var('REQUEST_URI', 'SE'),
	'vhosts'		=> $vhosts,
	'username'		=> $username,
	'proper'		=> $proper,
	'tmp_profile_id'	=> $profile_id,
	'admin'			=> $editor,
	'_RELEASE'		=> _RELEASE,
	'HAS_WHITELIST'		=> $has_whitelist,
	'HAS_SAVED_SCANS'	=> $has_saved_scans,
	'HAS_SPECIAL_PLUGINS'	=> $has_special_plugins,
	'HAS_CLUSTERS'		=> $has_clusters,
	'HAS_REGISTERED_COMPS'	=> $has_registered,
	'check_nessus'		=> $_chk->check_nessus(),
	'check_secure'		=> $_chk->check_secure()
));

$tpl->display('index.tpl');

?>
