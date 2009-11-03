<?php
/**
 * FormController.php
 *
 * @category   MyProject
 * @package    MyProject_App_Controller
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
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

        if ($this->getRequest()->isPost()) {

            // get form data
            $formData = $_POST;

            // ajax validation
            if ($this->isJson) {

                // test asynchron validation
                if (isset($formData['username'])) {
                    sleep(3);
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

                    $avatarFile = $form->getElement('avatar_file');
                    //$avatarFile->receive();
                    //$avatarFile->getMimeType();
                    //$avatarFile->getFileName();

                    // if it is ajax it will redirect anyway cause _redirect handle isAjax
                    $this->_redirect('/example/form/indexvalidate');
                }
            }
        }

        $this->view->form = $form;
    }

    public function indexvalidateAction()
    {

    }

    public function uploadAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->view->layout()->disableLayout(true);

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
        $registry = MyProject_Registry::getInstance();
        $locale = $registry->registry('locale');

        $search = $this->_getParam('search');
        $client = new Zend_Http_Client('http://clients1.google.com/complete/search', array( 'maxredirects' => 0, 'timeout' => 30));
        $client->setParameterGet('hl', substr($locale->toString(), 0, 2));
        $client->setParameterGet('js', 'true');
        $client->setParameterGet('q', $search);
        $reponse = $client->request(Zend_Http_Client::GET)->getBody();

        $matches = array();
        $results = array();
        if (preg_match_all('/window.google.ac.h\((.*)\)/is', $reponse, $matches) && isset($matches[1][0])) {
            $matchesResults = Zend_Json::decode($matches[1][0]);
            foreach ($matchesResults[1] as $matchesResultData) {
                $results[] = $matchesResultData[0];
            }
        }

        $this->_makeJson();
        $this->_setJson($results);
    }

    public function fancyselectAction()
    {
        $form = new MyProject_Form_Example_FancySelect();
        $form->setAction('/example/form/fancyelect');

        $this->view->form = $form;
    }

    public function dateselectAction()
    {
        $form = new MyProject_Form_Example_DateSelect();
        $form->setAction('/example/form/dateselect');

        if ($this->getRequest()->isPost()) {

            // get form data
            $formData = $_POST;

            // set form data
            $form->populate($formData);

            // check if all form is valid
            if ($form->isValid($formData)) {

            }
        }

        $this->view->form = $form;
    }

    public function autocompleterAction()
    {
        $form = new MyProject_Form_Example_AutoCompleter();
        $form->setAction('/example/form/autocompleter');

        if ($this->getRequest()->isPost()) {

            // get form data
            $formData = $_POST;

            // set form data
            $form->populate($formData);

            // check if all form is valid
            if ($form->isValid($formData)) {

            }
        }

        $this->view->form = $form;
    }
}

