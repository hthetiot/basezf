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

        $this->view->form = $form;
    }

    public function autocompletercallbackAction()
    {
        $this->_makeJson();
        $this->_setJson(array('Paris', 'Nantes'));
    }
}
