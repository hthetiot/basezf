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
BaseZF_Error_Handler::getInstance();

/**
 * Main BootStrap
 */
final class Bootstrap extends BaseZF_Bootstrap
{
    protected $_controllerModules = array(
        'default',
        'example',
    );

    protected function _getRoutes()
    {
        MyProject_Routes::fetch();
        return MyProject_Routes::fetch();
    }
}

// launch Bootstrap
Bootstrap::run();

