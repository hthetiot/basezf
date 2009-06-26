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
     * Instance of Zendlayout
     * @var object
     */
    protected $layout = null;

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
     * inited Response
     * @var boolean
     */
    protected $_initedReponse = false;

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
        // detect context if needed
        if (!isset($this->view->isAjax)) {

            $this->view->isAjax = $this->isAjax = $this->getRequest()->isXmlHttpRequest();
            $this->view->isJson = $this->isJson = $this->getRequest()->getHeader('X_REQUEST') == 'JSON';
        }

        $this->_initLayout();

    }

    /**
     * Initialize Layout object
     *
     * Initializes {@link $layout}
     *
     * @return Zendlayout
     */
    protected function _initLayout()
    {
        $this->layout = Zend_Layout::getMvcInstance();

        // set defaultLayout
        if (!is_null($this->_defaultLayout)) {
            $this->layout->setLayout($this->_defaultLayout);
        }
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

    public function postDispatch()
    {
        $this->_initResponse();
    }

    //
    // HTML Standard response helper
    //

    public function _makeHtml()
    {
        // set XHTML strict as default
        $this->view->doctype('XHTML1_STRICT');

        // configure view render for new view file suffix
        $this->_helper->viewRenderer->setNoRender(false);
        $this->_helper->viewRenderer->setViewSuffix('phtml');

        // set header
        $response = $this->getResponse();
        $response->setHeader('content-type', 'text/html', true);
    }

    //
    // Ajax response helper
    //

    /**
     * Init $this->isAjax value
     *
     * @return bool $this->isAjax value
     */
    protected function _initResponse()
    {
        if ($this->_initedReponse) {
            return;
        }

        if ($this->isJson) {
            $this->_makeJson();
        } else if ($this->isAjax) {
            $this->_makeAjax();
        } else {
            $this->_makeHtml();
        }
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
        $this->layout->setLayout('ajax');

        // disable view render
        $this->_helper->viewRenderer->setNoRender();

        // set controller properties
        $this->view->isAjax = $this->isAjax = true;
        $this->view->isAjaxHtml = $this->isAjaxHtml = false;

        // set response has inited
        $this->_initedReponse = true;

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
        $this->layout->setLayout('ajax');

        // configure view render for new view file suffix
        $this->_helper->viewRenderer->setNoRender(false);
        $this->_helper->viewRenderer->setViewSuffix('ajax.phtml');

        // set controller properties
        $this->view->isAjax = $this->isAjax = true;
        $this->view->isAjaxHtml = $this->isAjaxHtml = true;

        // set response has inited
        $this->_initedReponse = true;
    }

    //
    // Json response helper
    //

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
        $this->layout->setLayout('json');
        $this->_helper->viewRenderer->setNoRender();

        // set header
        $response = $this->getResponse();
        $response->setHeader('content-type', 'application/json', true);

        // set controller properties
        $this->view->isJson = $this->isJson = true;

        // set response has inited
        $this->_initedReponse = true;

        return $this;
    }
}

