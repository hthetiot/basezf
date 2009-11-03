<?php
/**
 * Example form object
 *
 * @category  MyProject
 * @package   MyProject_Form
 * @copyright Copyright (c) 2008 MyProject
 */

class MyProject_Form_Example_AjaxForm extends BaseZF_Framework_Form
{
    /**
     * Configure form
     */
    public function init()
    {
        $this->setAttrib('class', 'ajaxForm formAjaxValidate');

        $registry = MyProject_Registry::getInstance();
        $currentLocale = $registry->registry('locale');

        //
        // Login Information fields
        //

        $this->addElement('info', 'info3', array(
            'label'        => __('Login Information'),
            'messages'    => array(
                __('Your username and password must both be at least 8 characters long and are case-sensitive. Please do not enter accented characters.'),
                __('We recommend that your password is not a word you can find in the dictionary, includes both capital and lower case letters, and contains at least one special character (1-9, !, *, _, etc.).'),
                __('Your password will be encrypted and stored in our system. Due to the encryption, we cannot retrieve your password for you. If you lose or forget your password, we offer the ability to reset it.'),
            )
        ));

        $this->addElement('radio', 'gender', array(
            'helper'        => 'formMultiRadio',
            'label'         => __('Gender:'),
            'label_class'   => 'compact',
            'required'      => true,
            'multioptions'  => array(
                1 => __('Male'),
                2 => __('Female'),
            ),
        ));

        $this->getElement('gender')->setAttrib('label_class', 'compact');

        // login
        $this->addElement('text', 'username', array(
            'label'         => __('Username:'),
            'required'      => true,
            'description'     => __('May only contain letters, numbers, and underscore (_) and 8-20 characters long.'),
        ));

        $this->getElement('username')
            ->addValidator('StringLength', true, array(8, 20))
            ->addValidator('Regex', false, array(
                'pattern' => '/^[0-9A-Za-z+\_]*$/',
                'messages' => array(
                    'regexNotMatch'  => __('Invalide Username should only contain letters, numbers, and underscore (_).')
                ),
            ));

        // password
        $this->addElement('password', 'password', array(
            'label'         => __('Password:'),
            'required'      => true,
            'description'     => __('Must be 6-25 characters long.'),
        ));

        $this->getElement('password')
            ->addValidator('StringLength', true, array(6, 25));


        $this->addElement('password', 'password_check', array(
            'label'         => __('Please re-enter your password:'),
            'required'      => true,
            'description'     => __('Must match the password you entered just above.'),
        ));

        $this->getElement('password_check')->addValidator('StringEquals', true, array(
            'field1' => 'password',
            'field2' => 'password_check',
            'messages' => array(
                'notMatch'  => __("Passwords don't match, please enter them again")
            ),
        ));

        $this->addElement('checkbox', 'remember_me', array(
            'label'         => __('Remember Me'),
            'description'    => __("If you don't want to bother with having to login every time you visit the site, then checking \"Remember Me\" will place a unique identifier only our site can read that we'll use to identify you and log you in automatically each time you visit."),
        ));

        $this->addElement('checkbox', 'therms', array(
            'label'         => __('I agree to the bellow terms'),
            'required'      => true,
            'description'     => sprintf(
                __('By checking this, you are indicating that you are agree with the %s Terms of Use %s.'),
                '<a href="#">', '</a>'
            )
        ));

        $thermsRequired = new Zend_Validate_InArray(array(1));
        $thermsRequired->setMessage('You should accept the Terms of Use');
        $this->getElement('therms')->addValidator($thermsRequired);


        $this->addDisplayGroup(array(
            'info3',
            'gender',
            'username',
            'password',
            'password_check',
            'remember_me',
            'therms',
        ), 'login_information');

        $this->getDisplayGroup('login_information')->setLegend(__('Login Information'));

        // submit and reset buttons
        $this->addElement('reset', 'reset', array('label' => __('Cancel')))
             ->addElement('submit', 'update', array('label' => __('Submit')))
             ->addDisplayGroup(array('reset', 'update'), 'buttons');

        // add class for buttons
        $this->getDisplayGroup('buttons')->setAttrib('class', 'fieldsetButtons');
    }
}

