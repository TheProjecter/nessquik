<?php

session_name('nessquik');
session_start();

// Used for including files
if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(__FILE__)));
}

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/lib/settings.php');
require_once(_ABSPATH.'/lib/Smarty.php');
require_once(_ABSPATH.'/lib/functions.php');
require_once(_ABSPATH.'/db/nessquikDB.php');
require_once(_ABSPATH.'/lib/User.php');

$db 	= nessquikDB::getInstance();
$tpl	= SmartyTemplate::getInstance();
$usr	= User::getInstance();

$tpl->template_dir      = _ABSPATH.'/templates/';
$tpl->compile_dir	= _ABSPATH.'/templates_c/';

if(!$usr->is_editor($allowed_editors)) {
	exit;
}

if ($_POST) {
	$action = import_var('action', 'P');
} else {
	$action = import_var('action', 'G');
	switch($action) {
		case "make_nessusrc":
		case "make_machine_list":
			continue;
		default:
			exit;
	}
}

switch($action) {
	case "x_show_user_scans":
		require_once(_ABSPATH.'/lib/Scans.php');
		$scn = new Scans;

		$type 		= import_var('type', 'P');
		$scans		= array();

		switch($type) {
			case "pending":
			case "notready":
			case "running":
			case "finished":
				$scans = $scn->get_all_scans($type);
				break;
			default:
				$scans = $scn->get_all_scans("pending");
				break;
		}

		// Assigning PHP 5 to a template variable so that I can prevent displaying
		// the cancel scan if the user is not running php 5
		$tpl->assign(array(
			'scans'	=> $scans,
			'type'	=> $type
		));
		$tpl->display('admin_scans_list.tpl');
		break;
	case "x_count_scans":
		require_once(_ABSPATH.'/lib/Scans.php');

		$_scn = new Scans;

		$not_running	= 0;
		$pending 	= 0;
		$running 	= 0;
		$finished 	= 0;
		$all 		= 0;

		$not_running	= $_scn->count_not_running_scans();
		$pending	= $_scn->count_pending_scans();
		$running	= $_scn->count_running_scans();
		$finished	= $_scn->count_finished_scans();
		$all 		= $not_running + $pending + $running + $finished;

		echo "pass::$not_running;$pending;$running;$finished;$all;admin";
		break;
	case "show_scans_view":
		$type = import_var('type', 'P');

		$tpl->assign('type', $type);
		$tpl->display('admin_scans_viewer.tpl');
		break;
	case "show_scans_list":
		require_once(_ABSPATH.'/lib/User.php');

		$type 		= import_var('type', 'P');
		$refine_scan	= import_var('refine_scan', 'P');
		$scans		= array();
		$_usr		= User::getInstance();

		if ($refine_scan == "refine the list of scans by entering a username") {
			$refine_scan = '';
		} else if ($refine_scan == "refine the list of scans by entering a group") {
			$refine_scan = '';
		}

		$sql = array(
			'users' => "SELECT username,
					date_scheduled,
					date_finished,
					status
				FROM profile_list
				WHERE username LIKE ':1%'
				ORDER BY date_scheduled DESC
				LIMIT 25",
			'all' => "SELECT username,
					date_scheduled, 
					date_finished, 
					status 
				FROM profile_list 
				ORDER BY date_scheduled DESC
				LIMIT 25"
		);

		if ($refine_scan == "") {
			$stmt1 = $db->prepare($sql['all']);
			$stmt1->execute();
		} else {
			$stmt1 = $db->prepare($sql['users']);
			$stmt1->execute($refine_scan);
		}

		while($row = $stmt1->fetch_assoc()) {
			$date_scheduled = '';
			$date_finished	= '';

			if ($type == "group") {
				$users_group = $_usr->get_division_from_uid($row['username']);

				if (!preg_match("/^$refine_scan/i",$users_group))
					continue;
			}

			if ($row['date_scheduled'] != '') {
				$time_scheduled = strtotime($row['date_scheduled']);
				$date_scheduled = strftime("%Y-%m-%d at %I:%M %p", $time_scheduled);
			} else {
				$date_scheduled = '';
			}

			if ($row['date_finished'] != '') {
				$time_finished	= strtotime($row['date_finished']);
				$date_finished 	= strftime("%Y-%m-%d at %I:%M %p", $time_scheduled);
			} else {
				$date_finished	= '';
			}

			$scans[] = array(
				'user'		=> $row['username'],
				'scheduled'	=> $date_scheduled,
				'finished'	=> $date_finished,
				'status'	=> $row['status']
			);
		}

		$tpl->assign('type', $type);
		$tpl->assign('scans', $scans);
		$tpl->display('admin_scans_viewer_list.tpl');
		break;
	case "show_scans_view_history_and_dl":
		$type = import_var('type', 'P');

		$tpl->assign('type', $type);
		$tpl->display('admin_view_history_and_dl.tpl');
		break;
	case "show_scans_view_history_and_dl_list":
		$type 		= import_var('type', 'P');
		$refine_scan	= import_var('refine_scan', 'P');
		$scans		= array();

		if ($refine_scan == "refine the list of scans by entering a username") {
			$refine_scan = '';
		} else if ($refine_scan == "refine the list of scans by entering a group") {
			$refine_scan = '';
		}

		$sql = array(
			'users' => "SELECT DISTINCT(username)
				FROM profile_list
				WHERE username LIKE ':1%'
				ORDER BY username ASC
				LIMIT 25",
			'count' => "SELECT COUNT(profile_id)
				FROM profile_list 
				WHERE username = ':1'
				AND status=':2'",
			'all' => "SELECT DISTINCT(username)
				FROM profile_list 
				ORDER BY username ASC
				LIMIT 25"
		);

		if ($refine_scan == "") {
			$stmt1 = $db->prepare($sql['all']);
			$stmt1->execute();
		} else {
			$stmt1 = $db->prepare($sql['users']);
			$stmt1->execute($refine_scan);
		}

		$stmt2 = $db->prepare($sql['count']);

		while($row = $stmt1->fetch_assoc()) {
			$not_ready 	= 0;
			$pending 	= 0;
			$running 	= 0;
			$finished	= 0;
			$username	= $row['username'];

			$stmt2->execute($username, 'N');
			$not_ready = $stmt2->result(0);

			$stmt2->execute($username, 'P');
			$pending = $stmt2->result(0);

			$stmt2->execute($username, 'R');
			$running = $stmt2->result(0);

			$stmt2->execute($username, 'F');
			$finished = $stmt2->result(0);

			$not_ready = ($not_ready == 0) ? '' : $not_ready;
			$pending = ($pending == 0) ? '' : $pending;
			$running = ($running == 0) ? '' : $running;
			$finished = ($finished == 0) ? '' : $finished;

			$scans[] = array(
				'user'		=> $username,
				'not_ready'	=> $not_ready,
				'pending'	=> $pending,
				'running'	=> $running,
				'finished'	=> $finished
			);
		}

		$tpl->assign('type', $type);
		$tpl->assign('scans', $scans);
		$tpl->display('admin_view_history_and_dl_list.tpl');
		break;
	case "x_show_scan_history":
		$username 	= import_var('username', 'P');
		$results	= array();
		$sql = array(
			'scans' => "	SELECT pl.profile_id,us.setting_name 
					FROM profile_list AS pl
					LEFT JOIN profile_settings AS us
					ON pl.profile_id=us.profile_id 
					WHERE pl.username=':1';",

			'results' => "	SELECT results_id 
					FROM saved_scan_results 
					WHERE profile_id=':1'"
		);

		$stmt1 = $db->prepare($sql['scans']);
		$stmt2 = $db->prepare($sql['results']);

		$stmt1->execute($username);

		while($row = $stmt1->fetch_assoc()) {
			$has_results = false;
			$stmt2->execute($row['profile_id']);

			if ($stmt2->num_rows() > 0) {
				$has_results = true;
			}

			$results[] = array(
				'id'		=> $row['profile_id'],
				'name'		=> $row['setting_name'],
				'results'	=> $has_results
			);
		}

		$tpl->assign('section','general_admin');
		$tpl->assign('results',$results);
		$tpl->assign('view_username', $username);

		$tpl->display('view_scan_history.tpl');
		break;
	case "x_get_scan_results_list":
		$db 	= nessquikDB::getInstance();
		$sa 	= resultsDB::getInstance();

		$profile_id 	= import_var('profile_id','P');
		$results	= array();
		$sql = array(
			'results' => "	SELECT 	results_id,
						profile_id,
						saved_on
					FROM saved_scan_results
					WHERE profile_id = ':1'",

			'name' => "	SELECT setting_name
					FROM profile_settings
					WHERE profile_id=':1'"
		);

		$stmt1 = $sa->prepare($sql['results']);
		$stmt2 = $db->prepare($sql['name']);

		$stmt1->execute($profile_id);

		while($row = $stmt1->fetch_assoc()) {
			$results[] = array(
				'id'		=> $row['results_id'],
				'saved_on'	=> $row['saved_on']
			);
		}

		$stmt2->execute($profile_id);
		$setting_name = $stmt2->result(0);

		$tpl->assign('section', 'specific');
		$tpl->assign('profile_id', $profile_id);
		$tpl->assign('setting_name', $setting_name);
		$tpl->assign('results',$results);
		$tpl->display('view_scan_history.tpl');
		break;
	case "make_nessusrc":
		require_once(_ABSPATH.'/lib/ScanMaker.php');

                if(!include_once(_ABSPATH.'/lib/pear/HTTP/Download.php')) {
			die("Could not find the PEAR HTTP/Download.php file");
                }

		$profile_id	= import_var('profile_id');
		$_snm 		= new ScanMaker($profile_id);

		$_snm->scanner_set	= $_snm->getAllPlugins();
		$settings 		= $_snm->getProfileSettings($profile_id);
		$_snm->merge_severities();
		$_snm->merge_families();
		$_snm->merge_plugin_profiles();
		$_snm->merge_plugins();
		$_snm->merge_all();

		// Make the nessusrc file that contains scanner settings
		$output		= $_snm->get_nrc_file_data($_snm->scanner_set, $settings);
		$filename	= "nessusrc";
		$format		= "txt";

                $params = array(
                        'data'  		=> $output,
                        'cache' 		=> false,
                        'contenttype'   	=> 'application/octet-stream',
                        'contentdisposition'	=> array(HTTP_DOWNLOAD_ATTACHMENT, "$filename.$format"),
                );

		HTTP_DOWNLOAD::staticSend($params, false);
		break;
		break;
	case "make_machine_list":
		require_once(_ABSPATH.'/lib/ScanMaker.php');

                if(!@include_once(_ABSPATH.'/lib/pear/HTTP/Download.php')) {
			die("Could not find the PEAR HTTP/Download.php file");
                }

		$profile_id	= import_var('profile_id');
		$_snm 		= new ScanMaker($profile_id);

		// Make the machine list that specifies all the machines that need to be scanned
		$machine_list 	= $_snm->getMachines($profile_id);
		$output		= $_snm->get_ml_file_data($machine_list);
		$filename	= "machine-list";
		$format		= "txt";

                $params = array(
                        'data'  		=> $output,
                        'cache' 		=> false,
                        'contenttype'   	=> 'application/octet-stream',
                        'contentdisposition'	=> array(HTTP_DOWNLOAD_ATTACHMENT, "$filename.$format"),
                );

		HTTP_DOWNLOAD::staticSend($params, false);
		break;
}

?>
