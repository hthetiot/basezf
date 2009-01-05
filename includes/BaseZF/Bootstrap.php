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
    protected $_useRouterRewrite = false;

    protected $_controllerPlugins = array();

    protected $_controllerModules = array(
        'default',
        'example',
    );

    protected function __construct()
    {
        $this->_initLayout();

        $this->_initView();

        $this->_initFrontController();
    }

    static public function run()
    {
        new Bootstrap();

        Zend_Controller_Front::getInstance()->dispatch();
    }

    protected function _initView()
    {
        $view = Zend_Layout::getMvcInstance()->getView();

        // set view path
        $view->addScriptPath(PATH_TO_VIEWS);
        $view->addHelperPath(PATH_TO_HELPERS, 'MyProject_View_Helper');
        $view->addHelperPath(PATH_TO_INCLUDES . '/BaseZF/Framework/View/Helper', 'BaseZF_Framework_View_Helper');

        // set XHTML strict as default
        $view->doctype('XHTML1_STRICT');

        // set encoding
        $view->setEncoding('UTF-8');

		// configure css CDN
		$view->headLink()->enablePacks(CONFIG_STATIC_PACK_CSS);
		$view->headLink()->setPrefixSrc(CDN_URL_CSS);

		// configure js CDN
		$view->headScript()->enablePacks(CONFIG_STATIC_PACK_JS);
		$view->headScript()->setPrefixSrc(CDN_URL_JS);

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

        $controllerModules = array();
        foreach ($this->_controllerModules as $controllerModule) {
            $controllerModules[strtolower($controllerModule)] = PATH_TO_CONTROLLERS . '/' . ucfirst(strtolower($controllerModule));
        }

        $dispatcher->setControllerDirectory($controllerModules);

        // init front controller
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setRouter($router)
                        ->setDispatcher($dispatcher);
    }
}
