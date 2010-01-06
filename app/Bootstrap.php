<?php
/**
 * Bootstrap class in /app/
 *
 * Main Bootstrap
 *
 * @category  MyProject
 * @package   MyProject_App
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
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

