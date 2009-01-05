<?php
/**
 * Action.php
 *
 * @category   BaseZF_Framework
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_Framework_Controller_Action extends Zend_Controller_Action
{
    /**
     * Set default layout of controller
     * @var string
     */
    protected $_defaultLayout = 'default';

    /**
     * Instance of Zend_Layout
     * @var object
     */
    protected $_layout = null;

    /**
     * Ajax mode flag
     * @var boolean
     */
    public $isAjax;

    /**
     * Json mode flag
     * @var boolean
     */
    public $isJson;

    /**
     * AjaxHtml mode flag
     * @var boolean
     */
    public $isAjaxHtml;

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
        $this->_initAjax();

        $this->_initJson();

        $this->_initLayout();

        $this->_initModuleHelpers();
    }

    /**
     * Initialize Layout object
     *
     * Initializes {@link $layout}
     *
     * @return Zend_Layout
     */
    protected function _initLayout()
    {
        $this->_layout = Zend_Layout::getMvcInstance();

        // set default layout
        if ($this->isJson) {
            $this->_makeJson();
        } else if ($this->isAjax) {
            $this->_makeAjax();
        } else if (!is_null($this->_defaultLayout)) {
            $this->_layout->setLayout($this->_defaultLayout);
        }
    }

    /**
     * Add module Helper path
     *
     * @return $this for more fluent interface
     */
    protected function _initModuleHelpers($module = null)
    {
        if (is_null($module)) {
            $module = $this->getRequest()->getModuleName();
        }

        if (!empty($module) && strtolower($module) != 'default') {

            $module = ucfirst(strtolower($module));

            $this->view->addHelperPath(PATH_TO_HELPERS . '/' . $module, 'MyProject_View_Helper_' . $module);
        }

        return  $this;
    }

    //
    //
    //

    /**
     * Pre dispatching process to add right headers
     *
     * @return $this for more fluent interface
     */
    public function preDispatch()
    {
        // Modules/Controllers init
        $this->initModule();
        $this->initController();
    }

    /**
     * Initialize the module
     */
    public function initModule()
    {

    }

    /**
     * Controller specific initialization
     */
    public function initController()
    {
    }

    //
    // Ajax layout helpers
    //

    /**
     * Init $this->isAjax value
     *
     * @return bool $this->isAjax value
     */
    protected function _initAjax()
    {
        return $this->isAjax = $this->getRequest()->isXmlHttpRequest();
    }

    /**
     * Add javscript for ajax callback
     *
     * @return $this for more fluent interface
     */
    public function _addAjax($javascript)
    {
        $this->view->javascriptData()->addData($javascript);

        return $this;
    }

    /**
     * Configure the view for ajax rendering
     *
     * @return $this for more fluent interface
     */
    public function _makeAjax()
    {
        // set layout
        $this->_layout->setLayout('ajax');

        // disable view render
        $this->_helper->viewRenderer->setNoRender();

        // set controller properties
        $this->view->isAjax = $this->isAjax = true;
        $this->view->isAjaxHtml = false;
        $this->isAjaxHtml = false;

        return $this;
    }

    /**
     * Configure the view for ajax rendering
     * @note : not used for the momment
     *
     * @return $this for more fluent interface
     */
    public function _makeAjaxHtml()
    {
        // set layout
        $this->_layout->setLayout('ajax');

        // configure view render for new view file suffix
        $this->_helper->viewRenderer->setNoRender(false);
		$this->_helper->viewRenderer->setViewSuffix('ajax.phtml');

        // set controller properties
        $this->view->isAjax = $this->isAjax = true;
        $this->view->isAjaxHtml = $this->isAjaxHtml = true;

    }

    //
    // Json layout helpers
    //

    /**
     * Init $this->isJsons value
     *
     * @return bool $this->isJsons value
     */
    protected function _initJson()
    {
         return $this->view->isJson = $this->isJson = $this->getRequest()->getHeader('X_REQUEST') == 'JSON';
    }

    /**
     * Add json array to layout Json
     *
     * @return $this for more fluent interface
     */
    public function _addJson(array $json)
    {
        $this->view->jsonData()->addData($json);

        return $this;
    }

    /**
     * Add json array to layout Json
     *
     * @return $this for more fluent interface
     */
    public function _setJson(array $json)
    {
        $this->view->jsonData()->setData($json);

        return $this;
    }

    /**
     * Configure the view for ajax rendering
     *
     * @return $this for more fluent interface
     */
    public function _makeJson()
    {
        // set view render and layout
        $this->_layout->setLayout('json');
        $this->_helper->viewRenderer->setNoRender();

        // set header
        $response = $this->getResponse();
        $response->setHeader('content-type', 'application/json', true);

        // set controller properties
        $this->view->isJson = $this->isJson = true;

        return $this;
    }
}
