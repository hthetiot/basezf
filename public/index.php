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

// launch ErrorHandler
BaseZF_Error_Handler::registerErrorHandler();

 // load Bootstrap Class
Zend_Loader::loadClass('Bootstrap', PATH_TO_APPLICATION);

// run Bootstrap
$bootstrap = new Bootstrap(array(

	// somes path
	'path_to_controllers' 	=> PATH_TO_CONTROLLERS,
	'path_to_layout' 		=> PATH_TO_LAYOUTS,
	'path_to_helper' 		=> PATH_TO_HELPERS,
	'path_to_views' 		=> PATH_TO_VIEWS,

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
