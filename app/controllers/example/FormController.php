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
        $form->setAction('/example/form/index');

        $request = $this->getRequest();

        if ($request->isPost()) {

            // get form data
            $formData = $_POST;

            // ajax validation
            if ($this->isJson) {

                // test asynchron validation
                if (isset($formData['first_name'])) {
                    sleep(5);
                }

                // set output mode for json only
                $this->_makeJson();

                $response = $form->processJson($formData);
                $this->_setJson($response);

            // check if all form is valid before normal process
            } else {

                // set form data
                $form->populate($formData);

                // check if all form is valid
                if ($form->isValid($formData)) {

                    // success, do stuff with your data here!
                    // i'll just do a lame redirect here

                    /*
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->setDestination('/tmp/');
                    $upload->receive();
                    */

                    die('cool');
                }
            }
        }

        $this->view->form = $form;
    }

    public function uploadAction()
    {
        $adapter = new Zend_ProgressBar_Adapter_JsPush(array(
            'updateMethodName' => 'Zend_ProgressBar_Update',
            'finishMethodName' => 'Zend_ProgressBar_Finish'
        ));

        $progressBar = new Zend_ProgressBar($adapter, 0, 100);

        for ($i = 1; $i <= 100; $i++) {
            if ($i < 20) {
                $text = 'Just beginning';
            } else if ($i < 50) {
                $text = 'A bit done';
            } else if ($i < 80) {
                $text = 'Getting closer';
            } else {
                $text = 'Nearly done';
            }

            $progressBar->update($i, $text);
            usleep(100000);
        }

        $progressBar->finish();
    }

    public function autocompletercallbackAction()
    {
        $this->_makeJson();
        $this->_setJson(array('Paris', 'Nantes'));
    }
}

