<?php
/**
 * Bootstrap class in /app
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
     *
     * @return void
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
     *
     * @return array an array of Zend_Controller_Router_Route instance
     */
    protected function _getRoutes()
    {
        return MyProject_Routes::fetch();
    }
}

