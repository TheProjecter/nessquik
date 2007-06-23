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
require_once(_ABSPATH.'/db/nessquikDB.php');

$username 	= import_var('username','S');

if ($username == '') {
	exit;
}

$db 	= nessquikDB::getInstance();
$tpl	= SmartyTemplate::getInstance();

$tpl->template_dir      = _ABSPATH.'/templates/';
$tpl->compile_dir	= _ABSPATH.'/templates_c/';

$action = import_var('action', 'P');

switch($action) {
	case "x_single_computer":
		require_once(_ABSPATH.'/lib/User.php');
		$usr = User::getInstance();

		$username 	= import_var('username','S');
		$id		= $usr->get_id($username);
		$accepted	= $usr->get_accepted_machine_list($id);
		$devices	= array();

		if (count($accepted) < 1) {
			echo "You have no computers registered to you";
			return;
		}

		foreach ($accepted as $ip => $host) {
			$devices[] = array(
				'ip' 	=> $ip,
				'host'	=> $host
			);
		}

		$tpl->assign('device_type','registered');
		$tpl->assign('devices',$devices);
		$tpl->display('device_list.tpl');
		break;
	case "x_single_cluster":
		require_once(_ABSPATH.'/lib/Clusters.php');
		require_once(_ABSPATH.'/lib/User.php');

		$clu 	= Clusters::getInstance();
		$usr	= User::getInstance();
		
		$username 	= import_var('username','S');
		$id		= $usr->get_id($username);
		$accepted	= $clu->get_clusters($id);
		$devices	= array();

		if (count($accepted) < 1) {
			echo "You have no clusters registered to you";
			return;
		}

		foreach ($accepted as $cluster_id => $name) {
			$devices[] = array(
				'id' 	=> $cluster_id,
				'name'	=> $name
			);
		}

		$tpl->assign('device_type','cluster');
		$tpl->assign('devices',$devices);
		$tpl->display('device_list.tpl');
		break;
	case "x_whitelist":
		$username 	= import_var('username','S');
		$output 	= '';
		$devices	= array();

		$sql = array(
			'select' => "SELECT * FROM whitelist WHERE username=':1'"
		);

		$stmt = $db->prepare($sql['select']);

		$stmt->execute($username);

		if($stmt->num_rows() < 1) {
			echo "You have no whitelist entries";
			return;
		}

		while($row = $stmt->fetch_assoc()) {
			$id 	= $row['whitelist_id'];
			$entry	= $row['listed_entry'];

			$devices[] = array(
				'id'	=> $id,
				'entry'	=> $entry
			);
		}

		$tpl->assign('device_type','whitelist');
		$tpl->assign('devices',$devices);
		$tpl->display('device_list.tpl');
		break;
	case "x_saved":
		$username 	= import_var('username','S');

		$sql = array(
			'select' => "SELECT ust.setting_id,ust.setting_name,pl.status 
				FROM profile_settings AS ust 
				LEFT JOIN profile_list AS pl
				ON pl.profile_id = ust.profile_id
				WHERE ust.username=':1' AND ust.setting_type = 'user'"
		);

		$stmt = $db->prepare($sql['select']);

		$stmt->execute($username);

		if($stmt->num_rows() < 1) {
			echo "You have no saved scans";
			break;
		}

		$output = "<table>";

		while($row = $stmt->fetch_assoc()) {
			$id 	= $row['setting_id'];
			$name	= $row['setting_name'];
			$status = $row['status'];

			$devices[] = array(
				'id'		=> $id,
				'name'		=> $name,
				'status'	=> $status
			);
		}

		$tpl->assign('device_type', 'saved');
		$tpl->assign('devices', $devices);
		$tpl->display('device_list.tpl');
		break;
}

?>
