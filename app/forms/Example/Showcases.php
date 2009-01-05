<?php
/**
 * Example form object
 *
 * @category  MyProject_Form
 * @package   MyProject
 * @copyright Copyright (c) 2008 MyProject
 */

class MyProject_Form_Example_Showcases extends BaseZF_Framework_Form
{
    /**
     * Configure form
     */
    public function init()
    {
        $this->addElement('text', 'name', array(
            'label'         => __('Name:'),
            'required'      => true,
            'description'   => 'xxxxxxxxx',
        ));
    }
}
