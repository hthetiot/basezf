<?php
/**
 * Bootstrap.php
 *
 * Main Bootstrap
 *
 * @category   MyProject
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

/**
 * Main BootStrap
 */
final class Bootstrap extends BaseZF_Bootstrap
{
    /**
     * Init MyProject Bean class
     */
    protected function _initMyProject()
    {
        // set MyProject config as application config
        MyProject::setConfig($this->getOptions());

        // init language support
        MyProject::setCurrentLocale();
    }

    /**
     * Get available routes
     */
    protected function _getRoutes()
    {
        return MyProject_Routes::fetch();
    }
}

