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
require_once(_ABSPATH.'/lib/Help.php');

$db 	= nessquikDB::getInstance();
$sa 	= resultsDB::getInstance();
$_hlp	= Help::getInstance();
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
	case "show_help_categories":
		$categories = $_hlp->get_help_categories('G');
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
}

?>
