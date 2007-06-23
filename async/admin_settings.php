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
$_usr	= User::getInstance();

$tpl->template_dir      = _ABSPATH.'/templates/';
$tpl->compile_dir	= _ABSPATH.'/templates_c/';

$action 	= import_var('action', 'P');

if(!$_usr->is_editor($allowed_editors)) {
	exit;
}

switch($action) {
	case "x_plugin_search":
		$sql = array(
			'select' => "SELECT family,pl.pluginid,shortdesc,script_name 
				FROM plugins AS pl 
				LEFT JOIN nasl_names AS nn 
				ON pl.pluginid=nn.pluginid 
				WHERE shortdesc LIKE '%:1%' 
				ORDER BY shortdesc ASC 
				LIMIT 20",
			'select_full' => "SELECT family,pl.pluginid,shortdesc,script_name 
				FROM plugins AS pl 
				LEFT JOIN nasl_names AS nn 
				ON pl.pluginid=nn.pluginid 
				WHERE `desc` LIKE '%:1%' 
				ORDER BY shortdesc ASC 
				LIMIT 20",
			'select_full_multi' => "SELECT family,pl.pluginid,shortdesc,script_name 
				FROM plugins AS pl 
				LEFT JOIN nasl_names AS nn 
				ON pl.pluginid=nn.pluginid 
				WHERE ",
			'by_plugin' => "SELECT family,pl.pluginid,shortdesc,script_name 
				FROM plugins AS pl 
				LEFT JOIN nasl_names AS nn 
				ON pl.pluginid=nn.pluginid 
				WHERE pl.pluginid=':1'",
			'select_multi' => "SELECT family,pl.pluginid,shortdesc,script_name 
				FROM plugins AS pl 
				LEFT JOIN nasl_names AS nn 
				ON pl.pluginid=nn.pluginid 
				WHERE ",
			'listing' => "SELECT * FROM profile_settings 
				WHERE username=':1' AND setting_type = 'sys'"
		);
		$multi_arg 	= false;
		$plugins 	= array();
		$username 	= import_var('username','S');
		$search_for 	= import_var('search_for', 'P', 'search');
		$ids		= array();

		// If there was nothing sent, then there's no further need to process
		if ($search_for == '')
			break;

		// Retrieve the user's general settings
		$stmt6 = $db->prepare($sql['listing']);
		$stmt6->execute($username);
		$settings = $stmt6->fetch_assoc();

		/**
		* This block of code is used to provide a rudimentary sort
		* of intelligence to the script. It allows a user to input
		* multiple search terms and it will do it's best to find
		* the results that relate to the multiple terms.
		*
		* More code should be added to make the script aware of
		* if a user is possibly searching for both a string and
		* a plugin id.
		*
		* The terms, if they are strings, are searched for in the
		* shortdesc field. If the term is a number, it is searched
		* for in the pluginid,bugtrac1 or bugtrac2 field.
		*/
		if (strpos($search_for, ' ') !== false) {
			$args 		= explode(' ', $search_for);
			$inline_sql	= '';
			$multi_sql	= '';
			$count		= 1;

			foreach ($args as $key => $val) {
				if (is_numeric($val))
					$inline_sql .= "pluginid=':$count' OR";
				else
					$inline_sql .= " shortdesc LIKE '%:$count%' AND";

				$multi_sql .= " `desc` LIKE '%:$count%' AND";
				$count++;
			}

			$inline_sql = trim($inline_sql);
			$inline_sql = trim(substr($inline_sql,0,-3));

			$multi_sql = trim($multi_sql);
			$multi_sql = trim(substr($multi_sql,0,-3));

			$multi_arg = true;

			$sql['select_multi'] .= $inline_sql;
			$sql['select_multi'] .= " ORDER BY shortdesc ASC LIMIT 20";

			$sql['select_full_multi'] .= $multi_sql;
			$sql['select_full_multi'] .= " ORDER BY shortdesc ASC LIMIT 20";
		}

		/**
		* Because different sql could be run, I use a simple if
		* to check to see which sql code should run. The common
		* $stmt var is used so I dont need to repeat code in the
		* parsing of the returned data
		*/
		if ($multi_arg) {
			$stmt = $db->prepare($sql['select_multi']);
			$stmt->execute($args);
		} else if(is_numeric($search_for)) {
			$stmt = $db->prepare($sql['by_plugin']);
			$stmt->execute($search_for);
		} else {
			$stmt = $db->prepare($sql['select']);
			$stmt->execute($search_for);
		}

		/**
		* And here's where the data is parsed. I set up the data
		* using an associative array that Smarty will understand
		* when I choose to display the template.
		*/
		while($row = $stmt->fetch_assoc()) {
			if ($row['shortdesc'] == '')
				continue;

			$shortdesc = str_replace("'",'',$row['shortdesc']);
			$shortdesc = htmlentities($shortdesc,ENT_QUOTES);

			$plugins[] = array(
				'id'		=> $row['pluginid'],
				'shortdesc'	=> trim($shortdesc),
				'family'	=> $row['family'],
				'nasl'		=> $row['script_name']
			);

			$ids[] = $row['pluginid'];
		}

		/**
		* As a final bout of logic here, I also search in the full description
		* field. The reason being that some plugins may have weird short
		* descriptions and the logical content of the plugin is contained in
		* the full description.
		*
		* This full search should just by default be considered not very accurate
		*/
		if ($multi_arg) {
			$stmt1 = $db->prepare($sql['select_full_multi']);
			$stmt1->execute($args);
		} else {
			$stmt1 = $db->prepare($sql['select_full']);
			$stmt1->execute($search_for);
		}

		// Process the description stuff. It should go at the bottom because it
		// is likely not very accurate
		while($row = $stmt1->fetch_assoc()) {
			if ($row['shortdesc'] == '')
				continue;

			// Check to see if the plugin is already in the list
			if (in_array($row['pluginid'], $ids))
				continue;

			$shortdesc = str_replace("'",'',$row['shortdesc']);
			$shortdesc = htmlentities($shortdesc,ENT_QUOTES);

			$plugins[] = array(
				'id'		=> $row['pluginid'],
				'shortdesc'	=> trim($shortdesc),
				'family'	=> $row['family'],
				'nasl'		=> $row['script_name']
			);
		}

		$tpl->assign('plugins', $plugins);
		$tpl->assign('links', $links);

		// Set the short plugin listing
		if ($settings['short_plugin_listing'] == 1)
			$tpl->assign('short_plugin_listing', true);
		else
			$tpl->assign('short_plugin_listing', false);

		break;
	case "x_show_add_special_profile":
		$tpl->display('add_special_plugin_profile.tpl');
		break;
	case "do_add_special_plugin_profile":
		$special_plugin_name 	= import_var('special_plugin_name', 'P', 'special_plugin_name');
		$items			= import_var('item', 'P');
		$groups			= import_var('groups', 'P');
		$sql = array(
			'select_profile' => "	SELECT profile_id 
						FROM special_plugin_profile
						WHERE profile_name = ':1';",
			'add_profile' => "INSERT INTO special_plugin_profile (`profile_name`) VALUES (':1');",
			'add_items' => "INSERT INTO special_plugin_profile_items (
						`profile_id`,
						`plugin_type`,
						`plugin`) 
					VALUES (':1',':2',':3');",
			'add_groups' => "INSERT INTO special_plugin_profile_groups (`group_id`,`profile_id`) VALUES (':1',':2');",
			'all_groups' => "SELECT group_id FROM division_group_list WHERE group_name='All Groups';"
		);
		$special_plugin_name = substr($special_plugin_name, 0, 127);

		$stmt1 = $db->prepare($sql['select_profile']);
		$stmt2 = $db->prepare($sql['add_profile']);
		$stmt3 = $db->prepare($sql['add_items']);
		$stmt4 = $db->prepare($sql['all_groups']);
		$stmt5 = $db->prepare($sql['add_groups']);

		if (in_array('all', $groups)) {
			/**
			* Since the word 'all' is in the group list, blow away
			* the entire group list and specifically select the 'all groups' id.
			* There's no reason to worry about any other groups that may have
			* been chosen because 'all groups' trumps every other individual group
			*/
			$groups = array();

			// Get the group id for 'all groups'
			$stmt4->execute();

			// Store it by it's self in the groups array
			$groups[] = $stmt4->result(0);
		}

		$stmt1->execute($special_plugin_name);
		// Check for duplicates. The name of the plugin cannot be duplicated.
		// It makes no sense anyway to have two special plugins with the same name
		if ($stmt1->num_rows() > 0) {
			echo "dupe";
			return;
		}

		$stmt2->execute($special_plugin_name);
		$stmt1->execute($special_plugin_name);
		$profile_id = $stmt1->result(0);

		// No duplicates found. Add the profile
		foreach ($groups as $key => $group) {
			$stmt5->execute($group, $profile_id);
			// The following populates the plugins table
			foreach ($items as $key => $val) {
				$item	= '';
				$tmp 	= explode(':', $val);

				$prefix = $tmp[0];

				// Some plugin families can have colons in them
				if (count($tmp) > 2) {
					for ($x = 1; $x < count($tmp); $x++) {
						$item	.= $tmp[$x] . ':';
					}

					$item 	= trim(substr($item,0,-1));
				} else {
					$item	= trim($tmp[1]);
				}

				// Severity items are prefixed with an 's:'
				if ($prefix == 's') {
					// Remove the prefix before inserting into table
					$stmt3->execute($profile_id, 'sev', $item);

				// Family items are prefixed with an 'f:'
				} else if ($prefix == 'f') {
					// Remove the prefix before inserting into table
					$stmt3->execute($profile_id, 'fam', $item);

				// Regular plugins are prefixed with a 'p:'
				} else if ($prefix == 'p') {
					// Remove the prefix before inserting into table
					$stmt3->execute($profile_id, 'plu', $item);
				}
			}
		}

		echo "pass";
		break;
	case "x_show_admin_settings_general":
		$tpl->display('admin_settings_general.tpl');
		break;
	case "x_group_search":
		$search_for = import_var('search_for', 'P');

		// All the group names are stored in uppercase.
		$search_for = strtoupper($search_for);
		$sql = array(
			'groups' => "SELECT * FROM division_group_list WHERE group_name LIKE ':1%' ORDER BY group_name ASC;"
		);
		$groups	= array();
		$stmt1 	= $db->prepare($sql['groups']);
		$stmt1->execute($search_for);

		while ($row = $stmt1->fetch_assoc()) {
			$groups[] = array(
				'id'	=> $row['group_id'],
				'name'	=> $row['group_name']
			);
		}

		$tpl->assign('groups', $groups);
		$tpl->assign('group_action', 'add');
		$tpl->display('group_list.tpl');
		break;
	case "x_show_plugin_profiles":
		$profiles 	= array();
		$sql = array(
			'select' => "	SELECT * FROM special_plugin_profile ORDER BY profile_name"
		);

		$stmt1 = $db->prepare($sql['select']);
		$stmt1->execute();

		while($row = $stmt1->fetch_assoc()) {
			$profiles[] = array(
				'id'	=> $row['profile_id'],
				'name'	=> $row['profile_name']
			);
		}

		$tpl->assign('profiles', $profiles);
		$tpl->display('show_special_plugins.tpl');
		break;
	case "do_delete_plugin_profile":
		$profile_id	= import_var('profile_id', 'P');
		$sql = array(
			'delete_items' => "DELETE FROM special_plugin_profile_items WHERE profile_id=':1';",
			'delete_profile' => "DELETE FROM special_plugin_profile WHERE profile_id=':1';",
			'delete_groups' => "DELETE FROM special_plugin_profile_groups WHERE profile_id=':1';"
		);

		$stmt1 = $db->prepare($sql['delete_items']);
		$stmt2 = $db->prepare($sql['delete_profile']);
		$stmt3 = $db->prepare($sql['delete_groups']);

		$stmt1->execute($profile_id);
		$stmt2->execute($profile_id);
		$stmt3->execute($profile_id);

		echo "pass";
		break;
	case "x_show_scanners":
		$scanners 	= array();
		$sql = array(
			'select' => "	SELECT * FROM scanners ORDER BY name ASC"
		);

		$stmt1 = $db->prepare($sql['select']);
		$stmt1->execute();

		while($row = $stmt1->fetch_assoc()) {
			$scanners[] = array(
				'id'	=> $row['scanner_id'],
				'name'	=> $row['name'],
				'key'	=> $row['client_key']
			);
		}

		$tpl->assign('scanners', $scanners);
		$tpl->display('show_scanners.tpl');
		break;
	case "x_show_add_scanner":
		$tpl->display('add_scanner.tpl');
		break;
	case "do_add_scanner":
		$scanner_name	= import_var('scanner_name', 'P', 'scanner');
		$groups		= import_var('groups', 'P');
		$sql		= array(
			'insert' => "INSERT INTO scanners (`name`,`client_key`) VALUES (':1',':2');",
			'all_groups' => "SELECT group_id FROM division_group_list WHERE group_name='All Groups';",
			'scanner_id' => "SELECT scanner_id FROM scanners WHERE name=':1' AND client_key=':2';",
			'group_insert' => "INSERT INTO scanners_groups(`group_id`,`scanner_id`) VALUES (':1',':2');"
		);

		$scanner_name = substr($scanner_name, 0, 254);

		if ($scanner_name == '') {
			echo "fail_no_name";
			exit;
		}

		if (count($groups) < 1) {
			echo "fail_no_groups";
			exit;
		}

		$stmt1 = $db->prepare($sql['insert']);
		$stmt2 = $db->prepare($sql['all_groups']);
		$stmt3 = $db->prepare($sql['scanner_id']);
		$stmt4 = $db->prepare($sql['group_insert']);

		if (in_array('all', $groups)) {
			/**
			* Since the word 'all' is in the group list, blow away
			* the entire group list and specifically select the 'all groups' id.
			* There's no reason to worry about any other groups that may have
			* been chosen because 'all groups' trumps every other individual group
			*/
			$groups = array();

			// Get the group id for 'all groups'
			$stmt2->execute();

			// Store it by it's self in the groups array
			$groups[] = $stmt2->result(0);
		}

		$client_id = random_string(32);

		$stmt1->execute($scanner_name, $client_id);
		$stmt3->execute($scanner_name, $client_id);

		$scanner_id = $stmt3->result(0);

		foreach($groups as $key => $group_id) {
			$stmt4->execute($group_id,$scanner_id);
		}

		echo "pass";
		break;
	case "x_show_scanners_for_group":
		$group_id = import_var('group_id', 'P');
		$profiles = array();
		$sql = array(
			'select' => "SELECT scanner_id,name FROM scanners WHERE group_id=':1';",
			'group' => "SELECT group_name FROM division_group_list WHERE group_id=':1';"
		);

		$stmt1 = $db->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['group']);
		$stmt1->execute($group_id);
		$stmt2->execute($group_id);

		$group_name 	= $stmt2->result(0);

		while($row = $stmt1->fetch_assoc()) {
			$scanners[] = array(
				'id'	=> $row['scanner_id'],
				'name'	=> $row['name']
			);
		}

		$tpl->assign('scanners', $scanners);
		$tpl->assign('group_id', $group_id);
		$tpl->assign('group_name', $group_name);
		$tpl->display('show_scanners_list.tpl');
		break;
	case "do_delete_group_scanners":
		$group_id = import_var('group_id', 'P');
		$sql = array(
			'delete' => "DELETE FROM scanners WHERE group_id=':1';"
		);

		$stmt1 = $db->prepare($sql['delete']);

		$stmt1->execute($group_id);

		echo "pass::$group_id";
		break;
	case "x_show_edit_special_plugin_profile":
		$profile_id 	= import_var('profile_id', 'P');
		$group_counter	= 0;
		$plugin_counter	= 0;

		$sql = array(
			'plugin_profile' => "	SELECT 	profile_id,
							profile_name
						FROM special_plugin_profile
						WHERE profile_id=':1';",
			'group_count' => "SELECT COUNT(dgl.group_id)
					FROM special_plugin_profile_groups AS sppg 
					LEFT JOIN division_group_list AS dgl 
					ON dgl.group_id=sppg.group_id 
					WHERE sppg.profile_id=':1'",
			'profile_plugins' => "SELECT COUNT(*) FROM special_plugin_profile_items WHERE profile_id=':1';",
		);

		$stmt1 = $db->prepare($sql['plugin_profile']);
		$stmt2 = $db->prepare($sql['group_count']);
		$stmt3 = $db->prepare($sql['profile_plugins']);

		$stmt1->execute($profile_id);
		$stmt2->execute($profile_id);
		$stmt3->execute($profile_id);

		$group_counter 	= $stmt2->result(0);
		$plugin_counter	= $stmt3->result(0);

		$row 		= $stmt1->fetch_assoc();

		$tpl->assign(array(
			'profile_id'		=> $profile_id,
			'profile_name'		=> $row['profile_name'],
			'group_counter'		=> $group_counter,
			'plugin_counter'	=> $plugin_counter
		));

		$tpl->display('edit_special_plugin_profile.tpl');
		break;
	case "x_show_edit_scanner":
		$scanner_id 	= import_var('scanner_id', 'P');
		$group_counter	= 0;

		$sql = array(
			'scanner' => "SELECT * FROM scanners WHERE scanner_id=':1'",
			'group_count' => "SELECT COUNT(dgl.group_id)
					FROM scanners_groups AS sg
					LEFT JOIN division_group_list AS dgl
					ON dgl.group_id=sg.group_id
					WHERE sg.scanner_id=':1'"
		);

		$stmt1 = $db->prepare($sql['scanner']);
		$stmt2 = $db->prepare($sql['group_count']);

		$stmt1->execute($scanner_id);
		$stmt2->execute($scanner_id);

		$settings 	= $stmt1->fetch_assoc();
		$group_counter	= $stmt2->result(0);

		$tpl->assign(array(
			'scanner_id'	=> $scanner_id,
			'scanner_name'	=> $settings['name'],
			'client_key'	=> $settings['client_key'],
			'group_counter'	=> $group_counter
		));

		$tpl->display('edit_scanner.tpl');
		break;
	case "x_do_get_specific_groups":
		$page		= import_var('page', 'P');
		if ($page == "plugin_profile")
			$id 	= import_var('profile_id', 'P');
		else
			$id	= import_var('scanner_id', 'P');

		$count		= 1;
		$groups		= array();
		$sql = array(
			'plugin_profile' => "	SELECT 	dgl.group_id,
						dgl.group_name
					FROM special_plugin_profile_groups AS sppg 
					LEFT JOIN division_group_list AS dgl 
					ON dgl.group_id=sppg.group_id 
					WHERE sppg.profile_id=':1'",
			'scanner' => "	SELECT 	dgl.group_id,
						dgl.group_name
					FROM scanners_groups AS scn 
					LEFT JOIN division_group_list AS dgl 
					ON dgl.group_id=scn.group_id 
					WHERE scn.scanner_id=':1'",
		);

		if ($page == "plugin_profile") {
			$stmt1 = $db->prepare($sql['plugin_profile']);
		} else {
			$stmt1 = $db->prepare($sql['scanner']);
		}

		$stmt1->execute($id);

		while($row = $stmt1->fetch_assoc()) {
			$groups[] = array(
				'id'	=> $row['group_id'],
				'name'	=> $row['group_name'],
				'count' => $count
			);

			$count++;
		}

		$tpl->assign('groups', $groups);
		$tpl->assign('group_action', "remove");
		$tpl->display('group_list.tpl');
		break;
	case "do_delete_specific_scanner":
		$scanner_id	= import_var('scanner_id', 'P');
		$remaining	= 0;
		$sql = array(
			'select_group' => "SELECT group_id FROM scanners WHERE scanner_id=':1'",
			'delete_scanner' => "DELETE FROM scanners WHERE scanner_id=':1';",
			'remaining' => "SELECT COUNT(scanner_id) FROM scanners WHERE group_id=':1';",
			'update' => "UPDATE profile_settings SET scanner_id = NULL WHERE scanner_id=':1'"
		);

		$stmt1 = $db->prepare($sql['select_group']);
		$stmt2 = $db->prepare($sql['delete_scanner']);
		$stmt3 = $db->prepare($sql['remaining']);
		$stmt4 = $db->prepare($sql['update']);

		$stmt1->execute($scanner_id);
		$stmt2->execute($scanner_id);

		$group_id	= $stmt1->result(0);

		$stmt3->execute($group_id);
		$remaining 	= $stmt3->result(0);

		$stmt3->execute($scanner_id);

		echo "pass::$remaining::$group_id";
		break;
	case "do_edit_special_plugin_profile":
		$profile_id		= import_var('profile_id', 'P');
		$special_plugin_name 	= import_var('special_plugin_name', 'P', 'special_plugin_name');
		$items			= import_var('item', 'P');
		$groups			= import_var('groups', 'P');
		$sql = array(
			'update' => "UPDATE special_plugin_profile SET profile_name=':1' WHERE profile_id=':2';",
			'del_items' => "DELETE FROM special_plugin_profile_items WHERE profile_id=':1';",
			'del_groups' => "DELETE FROM special_plugin_profile_groups WHERE profile_id=':1';",
			'add_items' => "INSERT INTO special_plugin_profile_items (
						`profile_id`,
						`plugin_type`,
						`plugin`) 
					VALUES (':1',':2',':3');",
			'add_groups' => "INSERT INTO special_plugin_profile_groups (`group_id`,`profile_id`) VALUES (':1',':2');",
			'all_groups' => "SELECT group_id FROM division_group_list WHERE group_name='All Groups';"
		);

		if (!is_numeric($profile_id)) {
			echo "fail";
			return;
		}

		if ($special_plugin_name == '') {
			echo "fail";
			return;
		}

		if (count($items) < 1) {
			echo "fail";
			return;
		}

		if (count($groups) < 1) {
			echo "fail";
			return;
		}

		$stmt1 = $db->prepare($sql['update']);
		$stmt2 = $db->prepare($sql['del_items']);
		$stmt3 = $db->prepare($sql['add_items']);
		$stmt4 = $db->prepare($sql['all_groups']);
		$stmt5 = $db->prepare($sql['add_groups']);
		$stmt6 = $db->prepare($sql['del_groups']);

		$stmt1->execute($special_plugin_name, $profile_id);
		$stmt2->execute($profile_id);
		$stmt6->execute($profile_id);

		if (in_array('all', $groups)) {
			/**
			* Since the word 'all' is in the group list, blow away
			* the entire group list and specifically select the 'all groups' id.
			* There's no reason to worry about any other groups that may have
			* been chosen because 'all groups' trumps every other individual group
			*/
			$groups = array();

			// Get the group id for 'all groups'
			$stmt4->execute();

			// Store it by it's self in the groups array
			$groups[] = $stmt4->result(0);
		}

		// No duplicates found. Add the profile
		foreach ($groups as $key => $group) {
			$stmt5->execute($group, $profile_id);
			// The following populates the plugins table
			foreach ($items as $key => $val) {
				$item	= '';
				$tmp 	= explode(':', $val);

				$prefix = $tmp[0];

				// Some plugin families can have colons in them
				if (count($tmp) > 2) {
					for ($x = 1; $x < count($tmp); $x++) {
						$item	.= $tmp[$x] . ':';
					}

					$item 	= trim(substr($item,0,-1));
				} else {
					$item	= trim($tmp[1]);
				}

				// Severity items are prefixed with an 's:'
				if ($prefix == 's') {
					// Remove the prefix before inserting into table
					$stmt3->execute($profile_id, 'sev', $item);

				// Family items are prefixed with an 'f:'
				} else if ($prefix == 'f') {
					// Remove the prefix before inserting into table
					$stmt3->execute($profile_id, 'fam', $item);

				// Regular plugins are prefixed with a 'p:'
				} else if ($prefix == 'p') {
					// Remove the prefix before inserting into table
					$stmt3->execute($profile_id, 'plu', $item);
				}
			}
		}

		echo "pass";
		break;
	case "do_delete_scanner":
		$scanner_id	= import_var('scanner_id', 'P');

		if (!is_numeric($scanner_id)) {
			echo "fail";
			return;
		}

		$sql = array(
			'scanner' => "DELETE FROM scanners WHERE scanner_id=':1';",
			'groups' => "DELETE FROM scanners_groups WHERE scanner_id=':1';"
		);

		$stmt1 = $db->prepare($sql['scanner']);
		$stmt2 = $db->prepare($sql['groups']);

		$stmt1->execute($scanner_id);
		$stmt2->execute($scanner_id);

		echo "pass";
		break;
	case "do_edit_scanner":
		$scanner_name	= import_var('scanner_name', 'P', 'scanner');
		$scanner_id	= import_var('scanner_id', 'P');
		$client_key	= import_var('client_key', 'P');
		$groups		= import_var('groups', 'P');
		$sql		= array(
			'update' => "UPDATE scanners SET name=':1', client_key=':2' WHERE scanner_id=':3';",
			'all_groups' => "SELECT group_id FROM division_group_list WHERE group_name='All Groups';",
			'delete_groups' => "DELETE FROM scanners_groups WHERE scanner_id=':1'",
			'group_insert' => "INSERT INTO scanners_groups(`group_id`,`scanner_id`) VALUES (':1',':2');"
		);

		$scanner_name = substr($scanner_name, 0, 254);

		if ($scanner_name == '') {
			echo "fail_no_name";
			exit;
		}

		if (count($groups) < 1) {
			echo "fail_no_groups";
			exit;
		}

		$stmt1 = $db->prepare($sql['update']);
		$stmt2 = $db->prepare($sql['all_groups']);
		$stmt3 = $db->prepare($sql['delete_groups']);
		$stmt4 = $db->prepare($sql['group_insert']);

		if (in_array('all', $groups)) {
			/**
			* Since the word 'all' is in the group list, blow away
			* the entire group list and specifically select the 'all groups' id.
			* There's no reason to worry about any other groups that may have
			* been chosen because 'all groups' trumps every other individual group
			*/
			$groups = array();

			// Get the group id for 'all groups'
			$stmt2->execute();

			// Store it by it's self in the groups array
			$groups[] = $stmt2->result(0);
		}

		$stmt1->execute($scanner_name, $client_key,$scanner_id);
		$stmt3->execute($scanner_id);

		foreach($groups as $key => $group_id) {
			$stmt4->execute($group_id,$scanner_id);			
		}

		echo "pass";
		break;
	case "regenerate_client_key":
		$scanner_id	= import_var('scanner_id', 'P');
		$client_key 	= random_string(32);

		echo "pass::$client_key";
		break;
}

?>
