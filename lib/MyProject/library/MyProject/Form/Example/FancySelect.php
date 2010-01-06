<?php
/**
 * MyProject_Form_Example_DateSelect class in /MyProject/Form/Example
 *
 * @category  MyProject
 * @package   MyProject_Form
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/MyProject/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class MyProject_Form_Example_FancySelect extends BaseZF_Framework_Form
{
    /**
     * Configure form
     */
    public function init()
    {
        $this->addElement('info', 'info1', array(
            'label'     => __('Fancy Select Samples'),
            'messages'  => array(
                '@todo'
            ),
        ));

        $this->addElement('select', 'sexuality_id', array(
            'helper'        => 'formFancySelect',
            'label'         => __('Sexuality:'),
            'notice'        => __('Choose from the list'),
            'required'      => true,
            'multioptions'  => array(
                '1' => __('Hetero'),
                '2' => __('Bi'),
                '3' => __('Gay')
            ),
        ));

        $this->addElement('multiselect', 'lookingfor_id', array(
            'helper'        => 'formFancySelect',
            'label'         => __('Her for:'),
            'multiple'      => true,
            'show_choice'   => true,
            'notice'        => __('Choose from the list'),
            'required'      => true,
            'multioptions'  => array(
                '1' => __('Chatting'),
                '2' => __('Promote Myself'),
                '3' => __('Meeting new people'),
                '4' => __('Flirting'),
                '5' => __('Find the true love'),
            ),
        ));

        $this->addDisplayGroup(array(
            'info1',
            'sexuality_id',
            'lookingfor_id',
        ), 'personal_information');

        $this->getDisplayGroup('personal_information')->setLegend(__('Fancy Select Samples'));

        // submit and reset buttons
        $this->addElement('reset', 'reset', array('label' => __('Cancel')))
             ->addElement('submit', 'update', array('label' => __('Submit')))
             ->addDisplayGroup(array('reset', 'update'), 'buttons');

        // add class for buttons
        $this->getDisplayGroup('buttons')->setAttrib('class', 'fieldsetButtons');
    }
}

