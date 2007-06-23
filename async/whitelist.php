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

$db 	= nessquikDB::getInstance();
$tpl	= SmartyTemplate::getInstance();

$tpl->template_dir      = _ABSPATH.'/templates/';
$tpl->compile_dir	= _ABSPATH.'/templates_c/';

$action = import_var('action', 'P');

switch($action) {
	case "x_do_adduser":
		$username 	= import_var('username', 'P');

		// Remove all possible whitespace from the entry
		$wl_item 	= str_replace(' ','',import_var('wlitem', 'P'));

		$username = strtolower(trim($username));

		if ($username == '') {
			echo "fail";
			break;
		}

		if ($wl_item == '') {
			echo "fail";
			break;
		}

		$sql = array(
			'select' => "SELECT * FROM whitelist WHERE username=':1' AND listed_entry=':2';",
			'insert' => "INSERT INTO whitelist (`username`,`listed_entry`) VALUES (':1',':2');"
		);

		$stmt1 = $db->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['insert']);

		$stmt1->execute($username, $wl_item);

		if ($stmt1->num_rows() > 0)
			echo "dupe::$username";
		else {
			$stmt2->execute($username, $wl_item);
			echo "pass::$username";
		}
		break;
	case "x_refresh_users":
		$sql = array(
			'select' => "SELECT DISTINCT(username) FROM whitelist ORDER BY username ASC;"
		);
		$count = 0;
		$users = array();

		$stmt = $db->prepare($sql['select']);
		$stmt->execute();

		$data = "<table width='100%'>";

		while ($row = $stmt->fetch_assoc()) {
			$users[] = array(
				'username' => $row['username']
			);
		}

		$tpl->assign('users',$users);
		$tpl->display('whitelist_users.tpl');
		break;
	case "x_show_entry_info":
		$username 	= import_var('username', 'P');
		$data		= array();
		$sql = array(
			'select' => "SELECT * FROM whitelist WHERE username=':1' ORDER BY listed_entry ASC;"
		);

		$stmt = $db->prepare($sql['select']);

		$stmt->execute($username);

		while ($row = $stmt->fetch_assoc()) {
			$data[] = array(
				'id' 		=> $row['whitelist_id'],
				'username'	=> $row['username'],
				'entry'		=> $row['listed_entry']
			);
		}

		$tpl->assign('username', $username);
		$tpl->assign('entries', $data);
		$tpl->display('whitelist_entry_info.tpl');
		break;
	case "x_do_delete_whitelist_user":
		$username = import_var('username', 'P');

		$sql = array(
			'delete' => "DELETE FROM whitelist WHERE username=':1';"
		);

		$stmt = $db->prepare($sql['delete']);

		$stmt->execute($username);

		echo "pass";
		break;
	case "x_do_delete_whitelist_entry":
		$wlid 		= import_var('wlid', 'P');
		$remaining	= 0;
		$sql = array(
			'select' => "SELECT username FROM whitelist WHERE whitelist_id=':1' LIMIT 1;",
			'delete' => "DELETE FROM whitelist WHERE whitelist_id=':1';"
		);

		$stmt1 = $db->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['delete']);

		$stmt1->execute($wlid);
		$username = $stmt1->result(0);

		$stmt2->execute($wlid);

		// Re-run the select query to see if at least one item is left
		$stmt1->execute($wlid);
		$remaining = $stmt1->num_rows();

		echo "$username::pass::$remaining";
		break;
	case "x_copy_user":
		$from	= import_var('from', 'P');
		$to 	= import_var('to', 'P');

		$status = 'fail';

		if ($from == '') {
			echo $status;
			break;
		}

		if ($to == '') {
			echo $status;
			break;
		}

		$sql = array(
			'select' => "SELECT listed_entry FROM whitelist WHERE username=':1';",
			'insert' => "INSERT INTO whitelist (`username`,`listed_entry`) VALUES (':1',':2');",
			'select_entry' => "SELECT * FROM whitelist WHERE username=':1' AND listed_entry=':2';",
		);

		$stmt1 = $db->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['insert']);
		$stmt3 = $db->prepare($sql['select_entry']);

		$stmt1->execute($from);

		if ($stmt1->num_rows() > 0) {
			while ($row = $stmt1->fetch_assoc()) {
				$entry = $row['listed_entry'];

				$stmt3->execute($to, $entry);

				if ($stmt3->num_rows() > 0)
					continue;
				else
					$stmt2->execute($to, $entry);
			}
			$status = 'pass';
		} else {
			$status = 'none';
		}

		echo $status;
		break;
	case "x_rename_user":
		$from	= import_var('from', 'P');
		$to	= import_var('to', 'P');

		$status = 'fail';

		if ($from == '') {
			echo $status;
			break;
		}

		if ($to == '') {
			echo $status;
			break;
		}

		$sql = array(
			'select' => "SELECT * FROM whitelist WHERE username=':1';",
			'update' => "UPDATE whitelist SET username=':1' WHERE username=':2';"
		);

		$stmt1 = $db->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['update']);

		$stmt1->execute($to);

		if ($stmt1->num_rows() > 0) {
			echo "exists";
		} else {
			$stmt2->execute($to,$from);
			echo "pass";
		}
}

?>
