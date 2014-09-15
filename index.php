<?php
define('ROOT_FOLDER', realpath(dirname(__FILE__)).'/');
define('CORE_FOLDER', ROOT_FOLDER.'core/');
define('APPLICATION_FOLDER', ROOT_FOLDER.'application/');

include_once ROOT_FOLDER.'error.php';
include_once CORE_FOLDER.'factory.php';
include_once CORE_FOLDER.'base.php';
include_once CORE_FOLDER.'config.php';
include_once CORE_FOLDER.'controller.php';
include_once CORE_FOLDER.'url.php';
include_once CORE_FOLDER.'db/mysqli.php';
include_once CORE_FOLDER.'db/active_record.php';
include_once CORE_FOLDER.'views_loader.php';
include_once CORE_FOLDER.'models_loader.php';
include_once CORE_FOLDER.'model.php';
include_once CORE_FOLDER.'input.php';
include_once CORE_FOLDER.'route.php';
include_once CORE_FOLDER.'session.php';
include_once CORE_FOLDER.'files_upload.php';

$db = CoreFactory::getDB();
//$db->setQuery('SET NAMES "utf8"')->execute();

$config = CoreConfig::getInstance();

$controller = URL::segment(1);
if(!$controller) {
	if(!$config->get('default_controller')) {
		error('No default controller assigned');
		exit;
	}
	$controller = $config->get('default_controller');
}
$controller = strtolower($controller);

$controller_file = APPLICATION_FOLDER.'controllers/'
									 .$controller.'.php';
if(!file_exists($controller_file)) {
	error('Controller file does not exist', 404);
	exit;
}

include $controller_file;

$controller_class = ucfirst($controller).'Controller';
if(!class_exists($controller_class)) {
	error('Controller class does not exist', 404);
	exit;
}

if(URL::getMode() == 'non_sef') {
	$non_sef_path = URL::getNonSEFPath();
	$sef_path = URL::getSEFPath($non_sef_path);
	if($sef_path) {
		redirect($sef_path);
	}
}

$controller_obj = new $controller_class();

$task = URL::segment(2);
if(!$task) {
	$task = 'index';
}
$task = strtolower($task);

if (!method_exists($controller_obj, $task)) {
	error('Task does not exist in controller', 404);
	exit;
}

$controller_obj->$task();
