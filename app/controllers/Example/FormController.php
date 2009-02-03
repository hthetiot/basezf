<?php
/**
 * FormController.php
 *
 * @category   MyProject_Controller
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class Example_FormController extends BaseZF_Framework_Controller_Action
{
    /**
     * Set default layout
     */
    protected $_defaultLayout = 'example';

    public function indexAction()
    {
        $form = new MyProject_Form_Example_Showcases();

        $request = $this->getRequest();

        if ($request->isPost()) {

            // get form data
            $formData = $_POST;

            // ajax validation
            if ($this->isJson) {

                // set output mode for json only
                $this->makeJson();

                if (!$form->isValidPartial($formData)) {
                    $messages = $this->getMessages();
                    $this->addJson($messages);
                }

            // check if all form is valid before normal process
            } else {

                // set form data
                $form->populate($formData);

                // check if all form is valid
                if ($form->isValid($formData)) {

                    // success, do stuff with your data here!
                    // i'll just do a lame redirect here

                }
            }
        }

        $this->view->form = $form;
    }

    public function autocompletercallbackAction()
    {
        $this->_makeJson();
        $this->_setJson(array('Paris', 'Nantes'));
    }
}

