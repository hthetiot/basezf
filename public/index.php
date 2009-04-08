<?php
/**
 * index.php
 *
 * Main Bootstrap launcher
 *
 * @category   MyProject
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

// prevent missing auto_prepend_file from apache
if (!defined('PATH_TO_APPLICATION')) {
    require_once(realpath(dirname(__FILE__) . '/..') . '/includes/auto_prepend.php');
}

// launch ErrorHandler
BaseZF_Error_Handler::registerErrorHandler();

 // load Bootstrap Class
Zend_Loader::loadClass('Bootstrap', PATH_TO_APPLICATION);

// run Bootstrap
$bootstrap = new Bootstrap(array(

	// somes path
	'controller_path' 	=> PATH_TO_CONTROLLERS,
	'layout_path' 		=> PATH_TO_LAYOUTS,
	'view_helper_path'  => PATH_TO_HELPERS,
	'view_path' 		=> PATH_TO_VIEWS,

	// enable modules
	'controller_modules'	=> array(
		'default',
		'example',
	),
));

$bootstrap->dispatch();

/*
// displaying current used memory
echo bytes_to_human_size(memory_get_usage());
*/
