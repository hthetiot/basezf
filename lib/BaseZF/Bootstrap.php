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

abstract class BaseZF_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected $_options = array();

    /**
	 * Default Option values
	 */
	protected $_defaultOptions = array(

        // Application Options
        'application_path'		    => null,
        'application_environment'   => null,

        // Dependency Checking
		'zend_version'			=> '1.7.7',

        // Debug options
        'debug_enable'          => false,
        'debug_report'          => true,
        'debug_report_from'     => null,
        'debug_report_to'       => null,

		// Controller Options
		'controller_path'			=> '#application_path/controllers',
		'controller_helper_paths'	=> array(),
		'controller_plugins'		=> array(),
		'controller_modules'	=> array(
			'default',
			'example',
		),

		// View Options
		'view_path'				=> '#application_path/views',
		'view_script_suffix' 	=> '.phtml',
		'view_inflector' 		=> 'scripts/:module/:controller/:action.:suffix',
		'view_helper_paths' 	=> array(
			'View_Helper' 					=> '#view_path/helpers',
			'BaseZF_Framework_View_Helper' 	=>'BaseZF/Framework/View/Helper',
		),

		// Layout Options
		'layout_path'			=> '#view_path/layouts',
		'layout_default' 		=> 'default',
		'layout_content_key'	=> 'content',
		'layout_script_suffix' 	=> '.phtml',
		'layout_inflector' 		=> ':script/layout.:suffix',
	);

    public function run()
    {
        try {

            $frontController = Zend_Controller_Front::getInstance();
            $frontController->dispatch();

        } catch(Exception $e) {

            BaseZF_Error_Handler::debugException($e);
        }
    }



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
	protected function _initOption()
	{
        // get bootstrap options
        $options = $this->getApplication()->getOption('bootstrap');

        if (empty($options['path'])) {
            throw new BaseZF_Exception('No bootstrap path provided');
        }

        // set application_path from path
		$this->_defaultOptions['application_path'] = dirname($options['path']);

        // set environment from application environment
        $this->_defaultOptions['application_environment'] = $this->getApplication()->getEnvironment();

		// merge with defaults options
		$options = array_merge($this->_defaultOptions, $options);

        // map options keys alias
		$options = $this->_mapOption($options);

		// set current has options
		$this->_options = $options;
	}

    /**
     *
     */
	protected function _mapOption($options, array $keys = array())
	{
		if (empty($keys)) {
			$keys = $options;
		}

		foreach ($options as $key => &$value) {

			if (is_array($value)) {
				$value = self::_mapOption($value, $keys);
			} else {

				$matches = array();
				while(preg_match(':(#([a-z_]*)).*:', $value, $matches)) {
					$value = str_replace($matches[1], $keys[$matches[2]], $value);
				}
			}
		}

		return $options;
	}

	/**
	 *
	 */
	public function setOption($key, $value)
	{
		if (is_array($key)) {

			$results = array();
			foreach ($key as $id => $value) {
				$this->setOption($id, $value);
			}

		} else {
			$this->_options[$key] = $value;
		}

		return $this;
	}

	/**
	 *
	 */
	public function getOption($key)
	{
		if (is_array($key)) {

			$results = array();
			foreach ($key as $value) {
				$results[$value] = $this->getOption($value);
			}

			return $results;

		} else {

			if(!array_key_exists($key, $this->_options)) {
				throw new Exception('Undefined options key "' . $key . '"');
			}

			return $this->_options[$key];
		}
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
        $option = $this->getOption(array(
			'layout_path',
			'layout_default',
			'layout_content_key',
			'layout_inflector',
		));

        // init layout
		$layout = Zend_Layout::startMvc(array(
			'layoutPath' => $option['layout_path'],
			'layout'     => $option['layout_default'],
			'contentKey' => $option['layout_content_key'],
		));

        // set layout path and suffix
        $layout->setInflectorTarget($option['layout_inflector']);
    }

	/**
	 *
	 */
    protected function _initView()
    {
		// get view Option
        $option = $this->getOption(array(
			'view_path',
			'view_inflector',
			'view_helper_paths',
		));

        $view = Zend_Layout::getMvcInstance()->getView();

        // set view default path
        $view->setScriptPath($option['view_path']);

		// add view helper paths
		foreach ($option['view_helper_paths'] as $helperClass => $helperPath) {
			$view->addHelperPath($helperPath, $helperClass);
		}

	    // set encoding and other options
        $view->setEncoding('UTF-8');
        $view->doctype('XHTML1_STRICT');
        $view->headTitle()->setSeparator(' - ');

        // Optionure view render (path and suffix)
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $viewRenderer->setView($view)
					 ->setViewScriptPathSpec($option['view_inflector']);
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
        $frontController->setParam('env', $this->getOption('application_environment'));

        // use error handler
        if ($this->getOption('debug_enable')) {
            $frontController->throwExceptions(true);
        }

		return $this;
    }

	/**
	 * Init routes
	 */
    protected function _initRoute()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();

        $routes = $this->_getRoutes();
        foreach ($routes as $name => &$route) {
            $router->addRoute($name, $route);
        }

        return $this;
    }

	/**
	 * Init controllers modules
	 */
    protected function _initControllerModules()
    {
        $frontController = Zend_Controller_Front::getInstance();

        $option = $this->getOption(array(
			'controller_path',
			'controller_modules',
		));

		$controllerModules = array();
        foreach ($option['controller_modules'] as $controllerModule) {
			$controllerModule = strtolower($controllerModule);
            $controllerModules[$controllerModule] = $option['controller_path'] . '/' . $controllerModule;
        }

        $frontController->setControllerDirectory($controllerModules);

        return $this;
    }

	/**
	 * Init controllers plugins
	 */
    protected function _initControllerPlugins()
    {
        $frontController = Zend_Controller_Front::getInstance();

		$controllerPlugins = $this->getOption('controller_plugins');
        foreach ($controllerPlugins as $controllerPlugin => $option) {
             $plugin = new $controllerPlugin($option);
             $frontController->registerPlugin($plugin);
        }

        return $this;
    }
}

