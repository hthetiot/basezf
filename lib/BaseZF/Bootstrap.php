<?php
/**
 * Bootstrap.php
 *
 * Main Bootstrap
 *
 * @category   BaseZF
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_Bootstrap
{
    protected $_zendRequiredVersion = '1.7.6';

    protected $_projectClassName;

    protected $_controllerPlugins = array();

    protected $_controllerModules = array(
        'default',
        'example',
    );

    protected function __construct()
    {
        if (Zend_Version::compareVersion($this->_zendRequiredVersion) > 0) {
            throw new Exception('Require Zend Framework Version upper than ' . $this->_zendRequiredVersion . ', current is version ' . Zend_Version::VERSION);
        }

        $this->_initLayout();

        $this->_initView();

        $this->_initFrontController();
    }

    static public function run()
    {
        try {

            new Bootstrap();

            Zend_Controller_Front::getInstance()->dispatch();

        // catch exception
        } catch (Exception $e) {

            BaseZF_Error_Handler::printException($e);
        }
    }

    protected function _initView()
    {
        $view = Zend_Layout::getMvcInstance()->getView();

        // set view path
        $view->addScriptPath(PATH_TO_VIEWS);
        $view->addHelperPath(PATH_TO_HELPERS, 'View_Helper');
        $view->addHelperPath('BaseZF/Framework/View/Helper', 'BaseZF_Framework_View_Helper');

        // set encoding
        $view->setEncoding('UTF-8');

        // configure view render (path and suffix)
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setView($view)
					 ->setViewScriptPathSpec('scripts/:module/:controller/:action.:suffix')
					 ->setViewScriptPathNoControllerSpec(':action.:suffix');
    }

    protected function _initLayout()
    {
        // set layout options
        $options = array(
			'layout'     => 'default',
			'layoutPath' => PATH_TO_LAYOUTS,
			'contentKey' => 'content',
		);

        // init layout
		$layout = Zend_Layout::startMvc($options);

        // set layout path and suffix
        $layout->setInflectorTarget(':script/layout.:suffix');
    }

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

        // init Controller module
        $this->_initControllerModules($dispatcher);

        // init Controller module
        $this->_initControllerPlugins($frontController);

        // init routes
        $this->_initRoute($router);

		return $this;
    }

    /**
     * Return an array of routes
     */
    protected function _getRoutes()
    {
        return array();
    }

    protected function _initRoute(Zend_Controller_Router_Interface $router)
    {
        // init routes
        $routes = $this->_getRoutes();
        foreach ($routes as $name => & $route) {
            $router->addRoute($name, $route);
        }

        return $this;
    }

    protected function _initControllerModules(Zend_Controller_Dispatcher_Interface $dispatcher)
    {
        // init controllers modules
        $controllerModules = array();
        foreach ($this->_controllerModules as $controllerModule) {
            $controllerModules[strtolower($controllerModule)] = PATH_TO_CONTROLLERS . '/' . ucfirst(strtolower($controllerModule));
        }

        $dispatcher->setControllerDirectory($controllerModules);

        return $this;
    }

    protected function _initControllerPlugins(Zend_Controller_Front $frontController)
    {
        // init controllers plugins
        foreach ($this->_controllerPlugins as $controllerPlugin => $options) {
             $plugin = new $controllerPlugin($options);
             $frontController->registerPlugin($plugin);
        }

        return $this;
    }
}
