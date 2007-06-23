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
	case "x_plugin_search":
		$sql = array(
			'select' => "SELECT family,pl.pluginid,shortdesc,script_name 
				FROM plugins AS pl 
				LEFT JOIN nasl_names AS nn 
				ON pl.pluginid=nn.pluginid 
				WHERE shortdesc LIKE '%:1%' 
				ORDER BY shortdesc ASC 
				LIMIT 15",

			'select_full' => "SELECT family,pl.pluginid,shortdesc,script_name 
				FROM plugins AS pl 
				LEFT JOIN nasl_names AS nn 
				ON pl.pluginid=nn.pluginid 
				WHERE `desc` LIKE '%:1%' 
				ORDER BY shortdesc ASC 
				LIMIT 10",

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

			'by_nasl' => "	SELECT pl.*,nn.script_name 
					FROM nasl_names AS nn 
					LEFT JOIN plugins AS pl 
					ON nn.pluginid=pl.pluginid 
					WHERE nn.script_name LIKE ':1'
					AND nn.script_name LIKE '%.nasl';",

			'listing' => "SELECT * FROM profile_settings 
				WHERE username=':1' AND setting_type = 'sys'"
		);
		$multi_arg 	= false;
		$specific	= false;
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
				if (is_numeric($val)) {
					$inline_sql .= "pluginid=':$count' OR";
				} else {
					$inline_sql .= " shortdesc LIKE '%:$count%' AND";
				}

				$multi_sql .= " `desc` LIKE '%:$count%' AND";
				$count++;
			}

			$inline_sql = trim($inline_sql);
			$inline_sql = trim(substr($inline_sql,0,-3));

			$multi_sql = trim($multi_sql);
			$multi_sql = trim(substr($multi_sql,0,-3));

			$multi_arg = true;

			$sql['select_multi'] .= $inline_sql;
			$sql['select_multi'] .= " ORDER BY shortdesc ASC LIMIT 15";

			$sql['select_full_multi'] .= $multi_sql;
			$sql['select_full_multi'] .= " ORDER BY shortdesc ASC LIMIT 10";
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
			// this is a specific search, so no further searches
			// should be done after this
			$stmt = $db->prepare($sql['by_plugin']);
			$stmt->execute($search_for);

			$specific = true;
		} else if(strpos($search_for, ".nasl") !== false) {
			// this is a specific search, so no further searches
			// should be done after this
			$search_for = str_replace('.nasl', '', $search_for);
			$search_for = str_replace('*', '%', $search_for);

			$stmt = $db->prepare($sql['by_nasl']);
			$stmt->execute($search_for);

			$specific = true;
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
			if ($row['shortdesc'] == '') {
				continue;
			}

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

		if (!$specific) {
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
				if ($row['shortdesc'] == '') {
					continue;
				}

				// Check to see if the plugin is already in the list
				if (in_array($row['pluginid'], $ids)) {
					continue;
				}

				$shortdesc = str_replace("'",'',$row['shortdesc']);
				$shortdesc = htmlentities($shortdesc,ENT_QUOTES);

				$plugins[] = array(
					'id'		=> $row['pluginid'],
					'shortdesc'	=> trim($shortdesc),
					'family'	=> $row['family'],
					'nasl'		=> $row['script_name']
				);
			}
		}

		$tpl->assign('plugins', $plugins);
		$tpl->assign('links', $links);
		$tpl->assign('plugin_type', 'plugin');

		// Set the short plugin listing
		if ($settings['short_plugin_listing'] == 1) {
			$tpl->assign('short_plugin_listing', true);
		} else {
			$tpl->assign('short_plugin_listing', false);
		}

		/**
		* The displayed data is actually captured by Prototype and
		* inserted into the <div> tag that I tell it to insert into
		* in the javascript files contained in the templates/ directory
		*/
		$tpl->display('plugin_list.tpl');
		break;
	case "x_plugin_specific_search_severity":
		$sql = array(
			'select' => "SELECT DISTINCT(sev) FROM plugins ORDER BY sev ASC"
		);
		$counter = 0;
		$plugins = array();

		$stmt = $db->prepare($sql['select']);

		$stmt->execute();

		while($row = $stmt->fetch_assoc()) {
			$severity = trim($row['sev']);

			if ($severity == '')
				continue;

			$plugins[] = array(
				'severity' 	=> $severity,
				'counter'	=> $counter
			);

			$counter++;
		}

		$tpl->assign('plugins', $plugins);
		$tpl->assign('plugin_type', 'severity');
		$tpl->display('plugin_list.tpl');
		break;
	case "x_plugin_specific_search_family":
		$plugins = array();
		$sql = array(
			'select' => "SELECT DISTINCT(family) FROM plugins ORDER BY family ASC"
		);
		$counter = 0;

		$stmt = $db->prepare($sql['select']);

		$stmt->execute();

		while($row = $stmt->fetch_assoc()) {
			$family = trim($row['family']);

			if ($family == '')
				continue;

			$plugins[] = array(
				'family'	=> $family,
				'counter'	=> $counter
			);

			$counter++;
		}

		$tpl->assign('plugins', $plugins);
		$tpl->assign('plugin_type', 'family');
		$tpl->display('plugin_list.tpl');
		break;
	case "x_plugin_specific_search_special":
		require_once(_ABSPATH.'/lib/User.php');

		$_usr = User::getInstance();

		$plugins	= array();
		$username	= import_var('username', 'S');
		$division	= $_usr->get_division_from_uid($username);
		$division_id	= $_usr->get_division_id($division);
		$sql = array(
			'select' => "	SELECT * 
					FROM special_plugin_profile AS spp 
					LEFT JOIN special_plugin_profile_groups AS sppg 
					ON spp.profile_id=sppg.profile_id 
					WHERE sppg.group_id=':1' 
					OR sppg.group_id=':2';",
			'all_groups' => "SELECT group_id FROM division_group_list WHERE group_name='All Groups'"
		);
		$counter = 0;

		$stmt1 = $db->prepare($sql['select']);
		$stmt2 = $db->prepare($sql['all_groups']);

		// Pull back the 'all groups' group id
		$stmt2->execute();
		$all_groups = $stmt2->result(0);

		// Run the specific plugin profiles query with both the users group id and the 'all' group id
		$stmt1->execute($division_id, $all_groups);

		while($row = $stmt1->fetch_assoc()) {
			$plugins[] = array(
				'id'		=> $row['profile_id'],
				'name'		=> $row['profile_name'],
				'counter'	=> $counter
			);

			$counter++;
		}

		$tpl->assign('plugins', $plugins);
		$tpl->assign('plugin_type', 'special');
		$tpl->display('plugin_list.tpl');
		break;
	case "x_show_full_desc":
		$pluginid	= import_var('pluginid', 'P');
		$sql = array(
			'select' => "SELECT `desc` FROM plugins WHERE pluginid=':1'"
		);

		$stmt = $db->prepare($sql['select']);

		$stmt->execute($pluginid);

		$output = trim($stmt->result(0));

		if (substr($output,0,4) == "<br>")
			$output = substr($output,4);

		$output = str_replace("&nbsp;", ' ', $output);

		$output = trim($output);

		echo $output;
		break;
	case "x_plugin_in_severity":
		$sql = array(
			'select' => "SELECT sev FROM plugins WHERE sev=':1'"
		);
		$search_for = import_var('search_for', 'P');

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($search_for);

		if($stmt->num_rows() > 0)
			echo "true";
		else
			echo "false";
		break;
	case "x_plugin_in_family":
		$sql = array(
			'select' => "SELECT family FROM plugins WHERE family=':1'"
		);
		$search_for = import_var('search_for', 'P');

		$stmt = $db->prepare($sql['select']);
		$stmt->execute($search_for);

		if($stmt->num_rows() > 0)
			echo "true";
		else
			echo "false";
		break;
}

?>
