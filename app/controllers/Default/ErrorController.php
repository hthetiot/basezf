<?php
/**
 * ErrorController.php
 *
 * @category   MyProject_Controller
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class ErrorController extends BaseZF_Framework_Controller_Action
{

    public function initController()
    {
        // set header (disable json/ajax)
        $response = $this->getResponse();
        $response->setHeader('content-type', 'text/html', true);

        // configure view render for new view file suffix
        $this->_helper->viewRenderer->setNoRender(false);
		$this->_helper->viewRenderer->setViewSuffix('phtml');

        // set layout
        $this->_layout->setLayout('error');
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
        // Grab the error object from the request
        $errors = $this->_getParam('error_handler');

        // $errors will be an object set as a parameter of the request object,
        // type is a property
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = __('Page not found');
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = __('Application error');

                // send debug report
                MyProject::sendExceptionByMail($errors->exception);
                break;
        }

        // get config
        $config = MyProject::registry('config');

        // display debug ?
        if (!$config->debug->enable) {
            $errorCode = $this->getResponse()->getHttpResponseCode();
            $this->_forward('error' . $errorCode);
        }

        // pass the actual exception object to the view
        $this->view->exception = $errors->exception;

        // pass the request to the view
        $this->view->request   = $errors->request;
    }

    public function error500Action()
    {
    }

    public function error404Action()
    {
    }
}

