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
	/**
	 * Set Zend Framework Required Version
	 */
    protected $_zendRequiredVersion = '1.7.6';

	/**
	 * Project ClassName
	 */
    protected $_projectClassName;

	/**
	 * Available controller plugins
	 */
    protected $_controllerPlugins = array();

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
    public function __construct()
    {
        try {

            if (!version_compare($this->_zendRequiredVersion, Zend_Version::VERSION, '=')) {
                throw new Exception('Require Zend Framework Version ' . $this->_zendRequiredVersion . ', current is version ' . Zend_Version::VERSION);
            }

			$this->_init();

            $this->_initLayout();

            $this->_initView();

            $this->_initFrontController();

            Zend_Controller_Front::getInstance()->dispatch();

        // catch all exception
        } catch (Exception $e) {
            BaseZF_Error_Handler::printException($e);
        }
    }

	/**
	 * You can overide this function to exec some predispatch requirements
	 */
	protected function _init()
	{
	}

	/**
     * Get current defined Routes
     */
    abstract protected function _getRoutes();

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

    protected function _initRoute(Zend_Controller_Router_Interface $router)
    {
        // init routes
        $routes = $this->_getRoutes();
        foreach ($routes as $name => &$route) {
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
