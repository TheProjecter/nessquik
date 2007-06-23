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
require_once(_ABSPATH.'/lib/Help.php');

$db 	= nessquikDB::getInstance();
$tpl	= SmartyTemplate::getInstance();
$_usr	= User::getInstance();
$_hlp	= Help::getInstance();

$tpl->template_dir      = _ABSPATH.'/templates/';
$tpl->compile_dir	= _ABSPATH.'/templates_c/';

$action 		= import_var('action', 'P');

if(!$_usr->is_editor($allowed_editors)) {
	exit;
}

switch($action) {
	case "x_show_add_help_content":
		$admin_categories	= $_hlp->get_help_categories('A');
		$general_categories	= $_hlp->get_help_categories('G');

		$tpl->assign('admin_categories', $admin_categories);
		$tpl->assign('general_categories', $general_categories);
		$tpl->display('add_help.tpl');
		break;
	case "do_add_help_content":
		$question	= import_var('question', 'P');
		$answer 	= import_var('answer', 'P', 'htmlcontent');
		$category_id	= import_var('category_id', 'P');

		$sql = array(
			'insert' => "INSERT INTO help (`category_id`,`question`,`answer`) VALUES (':1',':2',':3');"
		);

		$stmt = $db->prepare($sql['insert']);
		$stmt->execute($category_id, $question, $answer);

		echo "pass";
		break;
	case "do_add_help_category":
		$category_name 		= import_var('category_name', 'P');
		$category_access	= import_var('category_access', 'P');

		
		$sql = array(
			'insert' => "INSERT INTO help_categories(`type`,`category`) VALUES (':1',':2');"
		);

		$stmt = $db->prepare($sql['insert']);
		$stmt->execute($category_access, $category_name);

		echo "pass";
		break;
	case "show_help_categories":
		$categories = $_hlp->get_help_categories('A');
		$tpl->assign('categories', $categories);
		$tpl->display('help_categories.tpl');
		break;
	case "show_help_topics":
		$category_id = import_var('category_id', 'P');

		$topics		= $_hlp->get_help_topics($category_id);
		$category_name 	= $_hlp->get_category_name($category_id);

		$tpl->assign('category_name', $category_name);
		$tpl->assign('topics', $topics);
		$tpl->assign('topic_count', count($topics));

		$tpl->display('help_topics.tpl');
		break;
	case "show_change_help_content":
		$type 		= import_var('type', 'P');
		$output		= '';
		$categories	= $_hlp->get_help_categories($type);

		foreach($categories as $key => $val) {
			$category_id	= $val['id'];
			$category_name	= $val['name'];
			$content	= $_hlp->get_help_topics($category_id);

			$tpl->assign(array(
				'category_id'	=> $category_id,
				'category_name'	=> $category_name,
				'content'	=> $content
			));

			$output .= $tpl->fetch('edit_help.tpl');
		}

		if ($output == '') {
			echo "<center>No categories or topics were found</center><br>";
		} else {
			echo $output;
		}
		break;
	case "do_delete_help_topic":
		$help_id = import_var('help_id', 'P');

		$_hlp->delete_help_topic($help_id);

		echo "pass";
		break;
	case "do_delete_help_category":
		$category_id = import_var('category_id', 'P');

		$_hlp->delete_category($category_id);

		echo "pass";
		break;
	case "edit_specific_help_topic":
		$help_id = import_var('help_id', 'P');

		$admin_categories	= $_hlp->get_help_categories('A');
		$general_categories	= $_hlp->get_help_categories('G');
		$help_topic		= $_hlp->get_topic_values($help_id);

		$tpl->assign(array(
			'help_id'		=> $help_topic['help_id'],
			'selected_category'	=> $help_topic['category_id'],
			'question'		=> htmlentities($help_topic['question'], ENT_QUOTES),
			'answer'		=> htmlentities($help_topic['answer'], ENT_QUOTES),
			'admin_categories'	=> $admin_categories,
			'general_categories'	=> $general_categories
		));
		$tpl->display('edit_help_topic.tpl');
		break;
	case "do_edit_specific_help_topic":
		$help_id	= import_var('help_id', 'P');
		$category_id 	= import_var('category_id', 'P');
		$question	= import_var('question', 'P');
		$answer		= import_var('answer', 'P', 'htmlcontent');

		$_hlp->edit_help_topic($help_id,$category_id,$question,$answer);

		echo "pass";
		break;
}

?>
