<?php
/**
 * Bootstrap.php
 *
 * Main Bootstrap
 *
 * @category   MyProject
 * @package    MyProject_App
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

/**
 * Main BootStrap
 */
final class Bootstrap extends BaseZF_Framework_Application_Bootstrap
{
    /**
     * Init MyProject Bean class
     */
    protected function _initMyProject()
    {
        $registry = MyProject_Registry::getInstance();

        // set MyProject config as application config
        $registry->setConfig($this->getOptions());

        // init language support
        $registry->setCurrentLocale();
    }

    /**
     * Get available routes
     */
    protected function _getRoutes()
    {
        return MyProject_Routes::fetch();
    }
}

