<?php
/**
 * BaseZF_Framework_Application_Bootstrap class in /BaseZF/Framework/Application
 *
 * Main Bootstrap
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

abstract class BaseZF_Framework_Application_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected $_options = array();

    /**
     * Default Option values
     */
    protected $_defaultOptions = array(

        // Debug optionss
        'debug'    => array(
            'enable'    => false,
            'report'    => array(
                'enable'    => false,
                'from'      => null,
                'to'        => null,
            ),
        ),

        // Controller Options
        'controller'    => array(
            'path'          => 'application/controllers',
            'helper_paths'  => array(),
            'plugins'       => array(),
            'modules'       => array(
                'default',
                'example',
            ),
        ),

        // View Options
        'view'    => array(
            'path'              => 'application/views',
            'script_suffix'     => 'phtml',
            'encoding'          => 'iso-8859-1',
            'inflector'         => ':module/:controller/:action.:suffix',
            'helper_paths'      => array(),
        ),

        // Static Pack Options
        'static_pack'       => array(
            'enable'        => false,
            'css_config'    => null,
            'script_config' => null,
        ),

        // Layout Options
        'layout'    => array(
            'path'          => 'application/views/layouts',
            'default'       => 'default',
            'content_key'   => 'content',
            'script_suffix' => 'phtml',
            'inflector'     => ':script/layout.:suffix',
        ),
    );

    /**
     * Get available routes
     */
    abstract protected function _getRoutes();

    //
    // Options
    //

    /**
     *
     */
    protected function _initOptions()
    {
        $options = $this->getOptions();
        $options = $this->mergeOptions($this->_defaultOptions, $options);

        $this->setOptions($options);

        return $this->_options;
    }

    //
    // View and Layout initilisation
    //

    /**
     *
     */
    protected function _initLayout()
    {
        // set layout Option
        $layoutOptions = $this->getOption('layout');

        // init layout
        $layout = Zend_Layout::startMvc(array(
            'layoutPath' => $layoutOptions['path'],
            'layout'     => $layoutOptions['default'],
            'contentKey' => $layoutOptions['content_key'],
        ));

        //set layout preffix
        $layout->setViewSuffix($layoutOptions['script_suffix']);

        // set layout path and suffix
        $layout->setInflectorTarget($layoutOptions['inflector']);

        return $layout;
    }

    /**
     *
     */
    protected function _initView()
    {
        // get view Option
        $viewOptions = $this->getOption('view');

        $view = Zend_Layout::getMvcInstance()->getView();

        // set view default path
        $view->setBasePath($viewOptions['path']);

        // add view helper paths
        foreach ($viewOptions['helper_paths'] as $helperClass => $helperPath) {
            $view->addHelperPath($helperPath, $helperClass);
        }

        // set encoding and other options
        $view->setEncoding($viewOptions['encoding']);
        $view->doctype('XHTML1_STRICT');
        $view->headTitle()->setSeparator(' - ');

        // Optionure view render (path and suffix)
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setView($view)
            ->setViewSuffix($viewOptions['script_suffix'])
            ->setViewScriptPathSpec($viewOptions['inflector']);


        return $view;
    }

    /**
     *
     */
    protected function _initStaticPack()
    {
        // get view Option
        $staticPackoptions = $this->getOption('static_pack');

        //its enable or disable not per tyoe cause its more easy for degin and security
        if (!$staticPackoptions['enable']) {
            return;
        }

        //
        // Static Pack
        //

        $view = Zend_Layout::getMvcInstance()->getView();

        $headLinkPackConfig = new BaseZF_Framework_Config_Yaml($staticPackoptions['css_config']);
        $view->headLink()->setPacksConfig($headLinkPackConfig->toArray());
        $view->headLink()->enablePacks();

        $headScriptPackConfig = new BaseZF_Framework_Config_Yaml($staticPackoptions['script_config']);
        $view->headScript()->setPacksConfig($headScriptPackConfig->toArray());
        $view->headScript()->enablePacks();

        return $view->headScript();
    }

    //
    // FrontController initilisation
    //

    /**
     *
     */
    protected function _initFrontController()
    {
        // init standart router
        $router = new Zend_Controller_Router_Rewrite();

        // init dispatcher with modules controllers
        $dispatcher = new Zend_Controller_Dispatcher_Standard();

        // init front controller
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setRouter($router)
                        ->setDispatcher($dispatcher);

        // set default params
        $frontController->setParam('env', $this->getOption('application', 'environment'));

        return $frontController;
    }

    /**
     * Init routes
     */
    protected function _initRouter()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();

        $routes = $this->_getRoutes();
        foreach ($routes as $name => &$route) {
            $router->addRoute($name, $route);
        }

        return $router;
    }

    /**
     * Init controllers modules
     */
    protected function _initControllerModules()
    {
        $frontController = Zend_Controller_Front::getInstance();

        $controllerOptions = $this->getOption('controller');

        $controllerModules = array();
        foreach ($controllerOptions['modules'] as $controllerModule) {
            $controllerModule = strtolower($controllerModule);
            $controllerModules[$controllerModule] = $controllerOptions['path'] . '/' . $controllerModule;
        }

        $frontController->setControllerDirectory($controllerModules);

        return $controllerModules;
    }

    /**
     * Init controllers plugins
     */
    protected function _initControllerPlugins()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $controllerOptions = $this->getOption('controller');

        $controllerPlugins = array();
        foreach ($controllerOptions['plugins'] as $plugin) {

             $pluginName = $plugin['plugin'];
             $pluginParams = (isset($writer['params'])) ? $writer['params'] : array();

             Zend_Loader::loadClass($pluginName);
             $pluginObj = new $pluginName($pluginParams);
             $frontController->registerPlugin($pluginObj);

             $controllerPlugins[$pluginName] = $pluginObj;
        }

        return $controllerPlugins;
    }

    //
    // Others
    //

    /**
     * Init Debug
     */
    protected function _initDebug()
    {
        $debugOptions = $this->getOption('debug');

        $frontController = Zend_Controller_Front::getInstance();
        $frontController->throwExceptions($debugOptions['enable']);
    }
}

