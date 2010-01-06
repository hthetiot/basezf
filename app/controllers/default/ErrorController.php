<?php
/**
 * IndexController class in /app/controllers/default
 *
 * @category  MyProject
 * @package   MyProject_App_Controller
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class ErrorController extends BaseZF_Framework_Controller_Action
{
    /**
     * Set default layout of controller
     * @var string
     */
    protected $_defaultLayout = 'error';

    /**
     * Error object provide by zend controller plugins error
     * @var object
     */
    protected $_errorHandler;

    /**
     * exec by preDispatch of parent Controller class
     *
     * @return void
     */
    public function initController()
    {
        // Grab the error object from the request
        $this->_errorHandler = $this->_getParam('error_handler');
        $this->view->errorHandler = $this->_errorHandler;
    }

    /**
     * errorAction() is the action that will be called by the "ErrorHandler"
     * plugin.  When an error/exception has been encountered
     * in a ZF MVC application (assuming the ErrorHandler has not been disabled
     * in your bootstrap) - the Errorhandler will set the next dispatchable
     * action to come here.  This is the "default" module, "error" controller,
     * specifically, the "error" action.  These options are configurable.
     *
     * @see http://framework.zend.com/manual/en/zend.controller.plugins.html
     *
     * @return void
     */
    public function errorAction()
    {
        // set header (disable json/ajax)
        $response = $this->getResponse();

        // force error 404 from throw exception width code 404
        if ($this->_errorHandler->exception->getCode() == 404) {
            $this->_errorHandler->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION;
        }

        $this->layout->setLayout($this->_defaultLayout);

        // $errors will be an object set as a parameter of the request object, type is a property
        switch ($this->_errorHandler->type) {

            // not found error (controller or action not found or exception code is 404)
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
            {
                $response->setHttpResponseCode(404);
                $this->_forward('notfound');
                $this->view->message = __('Page not found');
                break;
            }

            // application error by default
            default:
            {
                $response->setHttpResponseCode(500);
                $this->_forward('applicationerror');
                $this->view->message = __('Application error');

                // report error enable ?
                if (defined('DEBUG_REPORT') && DEBUG_REPORT) {
                    BaseZF_errorHandler::sendExceptionByMail($this->_errorHandler->exception, DEBUG_REPORT_FROM, DEBUG_REPORT_TO, DEBUG_REPORT_SUBJECT);
                }

                break;
            }
        }

        // send error handler to view
        $this->view->errorHandler = $this->_errorHandler;
    }

    /**
     * Display error to end user and report it by mail if enable
     *
     * @return void
     */
    public function applicationerrorAction()
    {
        // do nothings for security
    }

    /**
     * Display page not found to end User
     *
     * @return void
     */
    public function notfoundAction()
    {
        // do nothings for security
    }
}

