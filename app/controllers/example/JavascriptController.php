<?php
/**
 * Example_JavascriptController class in /app/controllers/example
 *
 * @category  MyProject
 * @package   MyProject_App_Controller
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class Example_JavascriptController extends BaseZF_Framework_Controller_Action
{
    /**
     * Set default layout
     */
    protected $_defaultLayout = 'example';

    public function indexAction()
    {
    }

    public function ajaxlinkAction()
    {
        if ($this->isAjax) {

           // callback for ajax link with HTML
           if ($this->_getParam('html') == 1) {

                // execute javascript on callback an return ajax view content
                $this->_makeAjaxHtml(); //-> the view file is now ajaxlink.ajax.phtml

                // add javascript
                $this->_addAjax('alert("you click on me");');
                $this->_addAjax('alert("you can use responseTree: " + responseTree);');
                $this->_addAjax('alert("you can use responseElements: " + responseElements);');
                $this->_addAjax('alert("you can use responseHTML: " + responseHTML);');
                $this->_addAjax('alert("you can use originElement: " + originElement);');

                $this->_addAjax('originElement.appendText(" (clicked)");');
                $this->_addAjax('$("ajaxLinkCallbackValue").set("html", responseHTML);');

           // callback for ajax link without HTML
           } else {

                // execute javascript on callback
                $this->_makeAjax(); //-> view render is now disable
                $this->_addAjax('alert("you click on me");');
                $this->_addAjax('alert("you can allso update me with originElement var");');

                $this->_addAjax('originElement.appendText(" (clicked)");');
           }
        }
    }

    public function ajaxformAction()
    {
        $form = new MyProject_Form_Example_AjaxForm();
        $form->setAction('/example/javascript/ajaxform');

        if ($this->getRequest()->isPost()) {

            // get form data
            $formData = $_POST;

            // ajax validation ?
            if ($this->isJson) {

                // set output mode for json only
                $this->_makeJson();

                $response = $form->processJson($formData);
                $this->_setJson($response);

            // check if all form is valid before normal process
            } else {

                // set form data
                $form->populate($formData);

                // check if all form is valid else add error in render with decorator
                if ($form->isValid($formData)) {

                    // success, do stuff with your data here!
                    // i'll just do a lame redirect here

                    // if it is ajax it will redirect anyway cause _redirect handle isAjax
                    $this->_redirect('/example/javascript/ajaxformvalidate');

                // ajax submit display form with error
                } else if ($this->isAjax) {

                    //  use ajaxform.ajax.phtml view file is it is ajax
                    $this->_makeAjaxHTML();
                    $this->_addAjax("originElement.set('html', responseHTML);");
                }
            }
        }

        $this->view->form = $form;
    }

    public function ajaxformvalidateAction()
    {
    }

    public function lightboxAction()
    {
    }

    public function uwaAction()
    {
    }

    public function progressbarAction()
    {
    }
}

