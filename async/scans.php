<?php

session_name('nessquik');
session_start();

// Used for including files
if (!defined("_ABSPATH")) {
	define("_ABSPATH", dirname(dirname(__FILE__)));
}

require_once(_ABSPATH.'/confs/config-inc.php');
require_once(_ABSPATH.'/lib/Smarty.php');
require_once(_ABSPATH.'/lib/functions.php');
require_once(_ABSPATH.'/lib/Nessus.php');
require_once(_ABSPATH.'/lib/Scans.php');

$db 	= nessquikDB::getInstance();
$sa 	= resultsDB::getInstance();
$tpl	= SmartyTemplate::getInstance();

$tpl->template_dir      = _ABSPATH.'/templates/';
$tpl->compile_dir	= _ABSPATH.'/templates_c/';

if ($_POST) {
	$action = import_var('action', 'P');
} else {
	$action = import_var('action', 'G');
	switch($action) {
		case "make_report":
			continue;
		default:
			exit;
	}
}

switch($action) {
	case "x_show_user_scans":
		$_scn = new Scans;

		$type 		= import_var('type', 'P');
		$username 	= import_var('username', 'S');
		$scans		= array();

		switch($type) {
			case "pending":
			case "notready":
			case "running":
			case "finished":
				$scans = $_scn->get_scans($type, $username);
				break;
			default:
				$scans = $_scn->get_scans("pending", $username);
				break;
		}

		$tpl->assign(array(
			'scans'	=> $scans,
			'type'	=> $type
		));
		$tpl->display('scans_list.tpl');
		break;
	case "x_remove_scan_profile":
		$profile_id 	= import_var('profile_id', 'P');
		$type		= import_var('type', 'P');
		$_scn		= new Scans();

		$_scn->remove_scan_profile($profile_id);

		echo "pass::$type";
		break;
	case "make_report":
		$nes = new Nessus();

                if(!@include_once(_ABSPATH.'/lib/pear/HTTP/Download.php')) {
			die("Could not find the PEAR HTTP/Download.php file");
                }

		$profile_id 	= import_var('profile_id', 'G');
		$username 	= import_var('username', 'S');
		$format		= import_var('format', 'G');
		$results_id	= import_var('results_id', 'G');

		$sql = array(
			'select' => "	SELECT scan_results 
					FROM saved_scan_results 
					WHERE results_id=':1'
					AND profile_id=':2'",

			'format' => "	SELECT report_format 
					FROM profile_settings 
					WHERE profile_id=':1' 
					AND username=':2';"
		);

		$stmt1 = $sa->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['format']);
		$stmt1->execute($results_id,$profile_id);

		if ($format == "default") {
			$stmt2->execute($profile_id,$username);
			$format = $stmt2->result(0);
		}

		$output = $stmt1->result(0);

		if ($format == 'txt') {
			$output = $nes->output_text($output, false);
		} elseif ($format == 'html') {
			$output = $nes->output_html($output, false);
		} elseif ($format == 'nbe') {
			$output = $nes->output_nbe($output, false);
		}

                $params = array(
                        'data'  		=> $output,
                        'cache' 		=> false,
                        'contenttype'   	=> 'application/octet-stream',
                        'contentdisposition'	=> array(HTTP_DOWNLOAD_ATTACHMENT, "scan_results.$format"),
                );

		HTTP_DOWNLOAD::staticSend($params, false);
		break;
	case "view_report":
		$profile_id 	= import_var('profile_id', 'P');
		$format		= import_var('format', 'P');
		$results_id	= import_var('results_id', 'P');
		$nes 		= new Nessus();

		$sql = array(
			'select' => "	SELECT 	results_id,
						scan_results 
					FROM saved_scan_results 
					WHERE profile_id=':1'
					AND results_id=':2' 
					ORDER BY results_id DESC 
					LIMIT 1;",

			'format' => "	SELECT report_format 
					FROM profile_settings 
					WHERE profile_id=':1' 
					AND username=':2';"
		);

		$stmt1 = $sa->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['format']);

		$stmt1->execute($profile_id,$results_id);

		if ($format == "default") {
			$stmt2->execute($profile_id,$username);
			$format = $stmt2->result(0);
		}

		$row = $stmt1->fetch_assoc();

		$content = $row['scan_results'];

		if ($format == 'txt') {
			/**
			* The 'true' here means that the html or text is embedded (which it is)
			* so nessquik will strip off the HTML,HEAD,BODY etc tags that
			* would be inappropriate for HTML, and will replace newline (\n) chars
			* in the text format with <br> tags
			*/
			echo $nes->output_text($content, true);
		} else if ($format == 'html') {
			echo $nes->output_html($content, true);
		} else if ($format == 'nbe') {
			echo $nes->output_nbe($content, true);
		}
		break;
	case "email_report":
		require_once(_ABSPATH.'/lib/User.php');

		$usr = User::getInstance();

		$profile_id 	= import_var('profile_id', 'P');
		$results_id	= import_var('results_id', 'P');
		$format		= import_var('format', 'P');
		$username 	= import_var('username', 'S');
		$email 		= $usr->get_email_from_uid($username);

		if ($email === false) {
			echo "fail";
			return;
		}

		$nes 		= new Nessus();

		$sql = array(
			'select' => "	SELECT scan_results 
					FROM saved_scan_results 
					WHERE profile_id=':1'
					AND results_id=':2'
					ORDER BY results_id DESC 
					LIMIT 1;",

			'format' => "	SELECT report_format 
					FROM profile_settings 
					WHERE profile_id=':1' 
					AND username=':2';"
		);

		$stmt1 = $sa->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['format']);
		$stmt1->execute($profile_id,$results_id);

		if ($stmt1->num_rows() < 1) {
			echo "fail";
			return;
		}

		$content	= $stmt1->result(0);

		if ($format == "default") {
			$stmt2->execute($profile_id,$username);
			$format = $stmt2->result(0);
		}

		if ($format == 'txt') {
			/**
			* The 'false' here means that the html or text is not embedded
			* so nessquik will not strip off the HTML,HEAD,BODY etc tags,
			* and will not replace newline (\n) chars in the text format 
			* with <br> tags
			*/
			$output	= $nes->output_text($content, false);
			$ext	= 'txt';
		} elseif ($format == 'html') {
			$output	= $nes->output_html($content, false);
			$ext	= 'html';
		} else if ($format == 'nbe') {
			$output	= $nes->output_nbe($content, false);
			$ext	= 'nbe';
		}

		/**
		* The empty field is the alternate recipients list.
		* The $ext value in the function call will work fine as long
		* as I dont changet the 'html' string above because that
		* is how the send_email function determines if it should
		* send HTML mail or text mail
		*/
		send_email($email, '', "Nessus Scan Results", $output, $ext);

		echo "pass";
		break;
	case "x_cancel_scan_profile":
		$profile_id 	= import_var('profile_id', 'P');
		$status 	= import_var('status','P');

		$sql = array(
			'update_pending' => "UPDATE profile_list SET status='N',cancel='N' WHERE profile_id=':1' AND status='P';",
			'update_running' => "UPDATE profile_list SET status='N',cancel='Y' WHERE profile_id=':1' AND status='R';"
		);

		if ($status == "running")
			$stmt1 = $db->prepare($sql['update_running']);
		else if ($status == "pending")
			$stmt1 = $db->prepare($sql['update_pending']);
		else if ($status == "saved_running_scan")
			$stmt1 = $db->prepare($sql['update_running']);
		else if ($status == "saved_pending_scan")
			$stmt1 = $db->prepare($sql['update_pending']);

		$stmt1->execute($profile_id);

		echo "pass::$status";
		break;
	case "x_reschedule_scan_profile":
		$profile_id	= import_var('profile_id', 'P');
		$time 		= strftime("%Y-%m-%d %T", time());

		$sql = array(
			'scanner_exists' => "	SELECT scanner_id
						FROM profile_settings
						WHERE profile_id=':1'",
			'update' => "	UPDATE profile_list 
					SET status='P', 
						date_scheduled=':1',
						cancel='N' 
					WHERE profile_id=':2';",
			'zero_progress' => "	UPDATE scan_progress 
						SET portscan_percent='0',
							attack_percent='0'
						WHERE profile_id=':1'"
		);

		$stmt1 = $db->prepare($sql['update']);
		$stmt2 = $db->prepare($sql['zero_progress']);
		$stmt3 = $db->prepare($sql['scanner_exists']);

		$stmt3->execute($profile_id);
		if (is_null($stmt3->result(0))) {
			echo "no_scanner";
			return;
		}

		$stmt1->execute($time,$profile_id);
		$stmt2->execute($profile_id);

		echo "pass";
		break;
	case "x_count_scans":
		require_once(_ABSPATH.'/lib/Scans.php');

		$_scn = new Scans;

		$username 	= import_var('username', 'S');
		$not_running	= 0;
		$pending 	= 0;
		$running 	= 0;
		$finished 	= 0;
		$all 		= 0;

		$not_running	= $_scn->count_not_running_scans($username);
		$pending	= $_scn->count_pending_scans($username);
		$running	= $_scn->count_running_scans($username);
		$finished	= $_scn->count_finished_scans($username);
		$all 		= $not_running + $pending + $running + $finished;

		echo "pass::$not_running;$pending;$running;$finished;$all";
		break;
	case "x_show_scan_history":
		$username 	= import_var('username', 'S');
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
			$stmt2->execute($row['profile_id']);

			if ($stmt2->num_rows() < 1) {
				continue;
			}

			$results[] = array(
				'id'	=> $row['profile_id'],
				'name'	=> $row['setting_name'],
			);
		}

		$tpl->assign('section','general');
		$tpl->assign('results',$results);

		$tpl->display('view_scan_history.tpl');
		break;
	case "x_show_compare":
		$username 	= import_var('username', 'S');
		$compare_step	= import_var('compare_step', 'P');
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
			$stmt2->execute($row['profile_id']);

			// Anything less than 2 scan results means there's really nothing to compare
			if ($stmt2->num_rows() < 2) {
				continue;
			}

			$results[] = array(
				'id'	=> $row['profile_id'],
				'name'	=> $row['setting_name'],
			);
		}

		$tpl->assign('section',$compare_step);
		$tpl->assign('results',$results);

		$tpl->display('view_compare_history.tpl');
		break;
	case "x_get_scan_results_list":
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
	case "x_get_scan_results_list_for_compare":
		$profile_id 	= import_var('profile_id','P');
		$compare_step	= import_var('compare_step', 'P');
		$results	= array();
		$sql = array(
			'results' => "	SELECT 	results_id,
						profile_id,
						saved_on
					FROM saved_scan_results
					WHERE profile_id = ':1'
					ORDER BY saved_on ASC",

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

		$tpl->assign(array(
			'section'	=> 'specific',
			'compare_step'	=> $compare_step,
			'profile_id'	=> $profile_id,
			'setting_name'	=> $setting_name,
			'results'	=> $results
		));

		$tpl->display('view_compare_history.tpl');
		break;
	case "x_remove_specific_scan_results":
		$results_id 	= import_var('results_id', 'P');
		$remaining	= 1;
		$sql = array(
			'select' => "SELECT profile_id FROM saved_scan_results WHERE results_id=':1'",
			'delete' => "DELETE FROM saved_scan_results WHERE results_id=':1'"
		);

		$stmt1 = $sa->prepare($sql['select']);
		$stmt2 = $sa->prepare($sql['delete']);

		$stmt1->execute($results_id);
		$profile_id 	= $stmt1->result(0);
		$remaining	= $stmt1->num_rows(0) - 1;

		$stmt2->execute($results_id);

		echo "pass::$profile_id::$remaining";
		break;
	case "do_compare_scans":
		require_once(_ABSPATH.'/lib/NessusCompareResults.php');

		$from			= import_var('from', 'P');
		$to			= import_var('to', 'P');
		$profile_id		= import_var('profile_id', 'P');

		$sql = array(
			'select' => "SELECT scan_results FROM saved_scan_results WHERE results_id=':1' AND profile_id=':2';",
			'profile_name' => "SELECT setting_name FROM profile_settings WHERE profile_id=':1';"
		);

		$stmt1 = $sa->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['profile_name']);

		$stmt1->execute($from, $profile_id);
		$from_nes	= new Nessus($stmt1->result(0));

		$stmt1->execute($to, $profile_id);
		$to_nes		= new Nessus($stmt1->result(0));

		$stmt2->execute($profile_id);
		$profile_name = $stmt2->result(0);

		$ncr		= new NessusCompareResults($from_nes, $to_nes, $profile_name);
		$ncr->compare_results();

		$results = $ncr->output_html();

		echo $results;
		break;
}

?>
