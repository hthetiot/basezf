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
        $registry = MyProject_Registry::getInstance();

        // set MyProject config as application config
        $registry->setConfig($this->getOptions());

        // init language support
        $registry->setCurrentLocale();
    }

    protected function _initZFDebug()
    {
        return;

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('ZFDebug');

        $registry = MyProject_Registry::getInstance();
        $db = $registry->registry('db');
        $dbCache = $registry->registry('cache');

        $options = array(
            'plugins' => array(
                'Variables',
                'Database'  => array('adapter' => $db),
                'File'      => array(
                    'base_path' => BASE_PATH,
                    'library'   => array(
                        'BaseZF'
                    ),
                ),
                'Memory',
                'Time',
                'Registry',
                'Cache'     => array('backend' => $dbCache->getBackend()),
                'Exception'
            ),
        );
        $debug = new ZFDebug_Controller_Plugin_Debug($options);

        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        $frontController->registerPlugin($debug);

    }

    /**
     * Get available routes
     */
    protected function _getRoutes()
    {
        return MyProject_Routes::fetch();
    }
}

