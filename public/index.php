<?php
/**
 * index.php
 *
 * Main Bootstrap
 *
 * @category   MyProject
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

// launch ErrorHandler
BaseZF_Error_Handler::replaceErrorHandler();

/**
 * Main BootStrap
 */
final class MyProject_Bootstrap extends BaseZF_Bootstrap
{
	/**
	 * Available controller modules
	 */
    protected $_controllerModules = array(
        'default',
        'example',
    );

	/**
	 * Initilize Bootstrap
	 */
	public function _init()
    {
		// init locales
		MyProject::registry('locale');
	}

	/**
     * Get current defined Routes
     */
    protected function _getRoutes()
    {
        return MyProject_Routes::fetch();
    }
}

// launch Bootstrap
new MyProject_Bootstrap();

