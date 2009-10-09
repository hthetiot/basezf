<?php
/**
 * Example form object
 *
 * @category  MyProject
 * @package   MyProject_Form
 * @copyright Copyright (c) 2008 MyProject
 */

class MyProject_Form_Example_AutoCompleter extends BaseZF_Framework_Form
{
    /**
     * Configure form
     */
    public function init()
    {
        $this->addElement('info', 'info1', array(
            'label'         => __('Auto Completer Sample'),
            'messages'      => array(
                '@todo',
            ),
        ));

        $this->addElement('text', 'search', array(
            'label' => __('Search:'),
        ));

        $this->addDisplayGroup(array(
            'info1',
            'search',
        ), 'auto_completer');

        $this->getDisplayGroup('auto_completer')->setLegend(__('Auto Completer Sample'));

        // submit and reset buttons
        $this->addElement('reset', 'reset', array('label' => __('Cancel')))
             ->addElement('submit', 'update', array('label' => __('Submit')))
             ->addDisplayGroup(array('reset', 'update'), 'buttons');

        // add class for buttons
        $this->getDisplayGroup('buttons')->setAttrib('class', 'fieldsetButtons');
    }
}

