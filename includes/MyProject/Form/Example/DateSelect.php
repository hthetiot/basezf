<?php
/**
 * Example form object
 *
 * @category  MyProject_Form
 * @package   MyProject
 * @copyright Copyright (c) 2008 MyProject
 */

class MyProject_Form_Example_DateSelect extends BaseZF_Framework_Form
{
    /**
     * Configure form
     */
    public function init()
    {
        $this->addElement('info', 'info1', array(
			'label'		=> __('Date Select Samples'),
			'messages'	=> array(
                '@todo',
			),
		));

        $this->addElement('date', 'date1', array(
			'label'     => __('Simple:'),
            'required'  => true,
		));

        $this->addElement('date', 'date2', array(
			'label'     => __('Special Format:'),
            'required'  => true,
            'format'    => array('d', 'm', 'Y')
		));

        $this->addElement('date', 'date3', array(
			'label'		=> __('Limited date range:'),
            'required'  => true,
            'years'     => range(date('Y')-1, date('Y')),
		));

		$this->addDisplayGroup(array(
            'info1',
			'date1',
            'date2',
            'date3',
		), 'personal_information');

        $this->getDisplayGroup('personal_information')->setLegend(__('Date Select Samples'));

        // submit and reset buttons
        $this->addElement('reset', 'reset', array('label' => __('Cancel')))
             ->addElement('submit', 'update', array('label' => __('Submit')))
             ->addDisplayGroup(array('reset', 'update'), 'buttons');

        // add class for buttons
        $this->getDisplayGroup('buttons')->setAttrib('class', 'fieldsetButtons');
    }
}

