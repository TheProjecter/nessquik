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
require_once(_ABSPATH.'/lib/Metrics.php');

$db 		= nessquikDB::getInstance();
$tpl		= SmartyTemplate::getInstance();
$_usr		= User::getInstance();
$_met		= Metrics::getInstance();

$tpl->template_dir      = _ABSPATH.'/templates/';
$tpl->compile_dir	= _ABSPATH.'/templates_c/';

if ($_GET) {
	$action 	= import_var('action', 'G');
} else {
	$action 	= import_var('action', 'P');
}

if(!$_usr->is_editor($allowed_editors)) {
	exit;
}

switch($action) {
	case "show_graph_categories":
		$categories = $_met->get_graph_categories();

		$tpl->assign(array(
			'categories'	=> $categories,
		));

		$tpl->display('metric_categories.tpl');
		break;
	case "show_report_categories":
		$categories = $_met->get_report_categories();

		$tpl->assign(array(
			'categories'	=> $categories,
		));

		$tpl->display('metric_categories.tpl');
		break;
	case "show_metric_config":
		$metric_id 	= import_var('metric_id', 'P');
		$class_name 	= $_met->get_metric_class($metric_id);
		$type		= $_met->get_metric_type($metric_id);
		$params		= $_POST;

		require_once(_ABSPATH.'/lib/metrics/'.$type.'/'.$class_name.'.php');

		$metric_class = new ReflectionClass($class_name);
		$metric = $metric_class->newInstance();

		$metric->is_admin(true);
		$metric->_prepare($params);
		$metric->_config($metric_id);
		break;
	case "view_metric":
		$metric_id 	= import_var('metric_id', 'G');
		$class_name 	= $_met->get_metric_class($metric_id);
		$type		= $_met->get_metric_type($metric_id);
		$params		= $_GET;

		require_once(_ABSPATH.'/lib/metrics/'.$type.'/'.$class_name.'.php');

		$metric_class = new ReflectionClass($class_name);
		$metric = $metric_class->newInstance();

		$metric->is_admin(true);
		$metric->_prepare($params);
		$metric->_create();
		break;
}

?>
