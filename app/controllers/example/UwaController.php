<?php
/**
 * UwaController.php
 *
 * @category   MyProject_Controller
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class Example_UwaController extends BaseZF_Framework_Controller_Action_Uwa
{
    public function indexAction()
    {
        // se widget title (it will translate)
        $this->_setTitle('My Sample Widget');

        // set widget metas
        $this->_setWidgetMetaValue('author', 'Harold Thetiot');
        $this->_setWidgetMetaValue('description', 'A simple Widget example');

        // set widget pref
        $this->_addPreference('my_text', 'text', 'My text pref');
        $this->_addPreference('my_password', 'password', 'My password pref');
        $this->_addPreference('my_checkbox', 'checkbox', 'My checkbox pref');
        $this->_addPreference('my_hidden', 'hidden');
        $this->_addPreference('my_range', 'range', 'My range pref', 10, array(
            'step' => '5',
            'min' => '5',
            'max' => '15',
        ));

        /*
        $this->_addPreference('my_list', 'list', 'My list pref', 1, array(
            '1' => 'one',
            '2' => 'two',
        ));
        */

        // disable debug
        $this->_enableDebug();
    }

    public function jsoncallbackAction()
    {
        $this->_makeJson();

        $this->_setJson(array('date' => date('Y-m-d H:i:s')));
    }

    public function ajaxcallbackAction()
    {
        $this->_makeAjaxHtml();
    }
}
