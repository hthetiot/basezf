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
        $this->_addPreference('mytext', 'text', 'My text pref');
        $this->_addPreference('myboolean', 'boolean', 'My boolean pref');
        $this->_addPreference('myhidden', 'hidden', 'My hidden pref');
        //$this->_addPreference('myrange', 'range', 'My range pref', array());
        //$this->_addPreference('mylist', 'list', 'My list pref');
        $this->_addPreference('mypassword', 'password', 'My password pref');

        // enable debug
        $this->_enableDebug();

        // disable debug
        $this->_enableDebug(false);
    }
}
