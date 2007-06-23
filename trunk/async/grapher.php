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

if ($_GET) {
	$action = import_var('action', 'G');
} else {
	$action = import_var('action', 'P');
}

switch($action) {
	case "show_metric_config":
		$metric_id 	= import_var('metric_id', 'P');
		$class_name 	= $_met->get_metric_class($metric_id);
		$type		= $_met->get_metric_type($metric_id);

		require_once(_ABSPATH.'/lib/metrics/'.$type.'/'.$class_name.'.php');

		$metric_class = new ReflectionClass($class_name);
		$metric = $metric_class->newInstance();

		$metric->is_admin(false);
		$metric->_prepare($params, false);
		$metric->_config($metric_id);
	case "view_metric":
		$metric_id 	= import_var('metric_id', 'P');
		$class_name 	= $_met->get_metric_class($metric_id);
		$type		= $_met->get_metric_type($metric_id);

		require_once(_ABSPATH.'/lib/metrics/'.$type.'/'.$class_name.'.php');

		$metric_class = new ReflectionClass($class_name);
		$metric = $metric_class->newInstance();

		$metric->is_admin(false);
		$metric->_prepare($params, false);
		$metric->_create(true);
		break;
}

?>
