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
		//
		// Personal Information fields
		//

		// @todo
		$this->addElement('info', 'info1', array(
			'label'		=> __('Personal Informationx'),
			'messages'	=> array(
				__('Please enter your name and address as they are listed for your debit card, credit card, or bank account.'),
			),
		));

		$this->addElement('radio', 'gender', array(
            'label'         => __('Gender:'),
		    'required'      => true,
			'multioptions'  => array(
				1 => __('Male'),
				0 => __('Female'),
			),
        ));

		$this->getElement('gender')->setAttrib('label_class', 'compact');

        $this->addElement('text', 'first_name', array(
            'label'         => __('First Name:'),
            'required'      => true,
        ));

        $this->addElement('text', 'last_name', array(
            'label'         => __('Last Name:'),
            'required'      => true,
        ));

		// @todo
		$this->addElement('date', 'birthday', array(
			'label'		=> __('Birthday:'),
		));

		$this->addElement('text', 'addr1', array(
            'label'         => __('Address:'),
        ));

		$this->addElement('text', 'addr2', array(
        ));

		$this->addElement('select', 'country_id', array(
			'label'         => __('Country:'),
			'multioptions'  => array(),
        ));

		$this->addElement('select', 'state_id', array(
			'label'         => __('State:'),
			'multioptions'  => array(),
        ));

		$this->addElement('text', 'zipcode', array(
            'label'         => __('Zip/Postal Code:'),
        ));

		$this->addElement('text', 'city', array(
            'label'         => __('City:'),
        ));

		$this->addElement('select', 'sexuality_id', array(
			'label'         => __('Sexuality'),
			'multioptions'  => array(),
        ));

		$this->addElement('select', 'lookingfor_id', array(
			'label'         => __('Here For:'),
			'multioptions'  => array(),
        ));

		$this->addDisplayGroup(array(
			'info1',
			'gender',
			'first_name',
			'last_name',
			'birthday',
			'addr1',
			'addr2',
			'country_id',
			'state_id',
			'zipcode',
			'city',
			'sexuality_id',
			'lookingfor_id',
		), 'personal_information');

        $this->getDisplayGroup('personal_information')->setLegend(__('Personal Information'));

		//
		// Contact Information fields
		//

		// @todo
		$this->addElement('info', 'info2', array(

		));

		$this->addElement('radio', 'how_contact', array(
			'helper'		=> 'formMultiRadio',
            'label'         => __('How to Contact You ?'),
            'required'      => true,
			'multioptions'  => array(
				1 => __('Phone'),
				0 => __('Email'),
			)
        ));

		$this->getElement('how_contact')->setAttrib('label_class', 'compact');


		$this->addElement('text', 'email', array(
            'label'         => __('Email:'),
            'required'      => true,
			'description'	=> __('We will never sell or disclose your email address to anyone. Once your account is setup, you may add additional email addresses.'),
        ));

		$this->addElement('text', 'email_check', array(
            'label'         => __('Re-enter Email:'),
            'required'      => true,
			'description'	=> __('Must match the email address you just entered above.'),
        ));

		$this->addElement('text', 'phone', array(
            'label'         => __('Phone:'),
        ));

		$this->addElement('text', 'fax', array(
            'label'         => __('Fax:'),
        ));

		$this->addElement('radio', 'subject', array(
			'helper'		=> 'formMultiRadio',
            'label'         => __('Message Subject:  '),
            'multioptions'  => array(
				1 => __('Help, my brother/sister is driving me crazy!'),
				1 => __("How can I tell my father/mother, it's time for them to retire?"),
				2 => __("I'm exasperated with an awkward partner!"),
				3 => __("How do I stop my family members from interfering?"),
				4 => __("Other:"),
			),

			// extras
			'container_class'	=> 'wide',
        ));

		$this->addElement('textarea', 'message', array(
            'label'         => __('Your Message:'),
			'required'      => true,
			'description'	=> __('Must be 250 characters or less.'),
        ));

		$this->addElement('textarea', 'message_wide', array(
            'label'         => __('Your Message:'),
			'required'      => true,
			'description'	=> __("We'd love to get your feedback on any of the products or services we offer or on your experience with us."),

			// extras
			'container_class'	=> 'wide',
		));

		$this->addElement('text', 'keywords', array(
            'label'         	=> __('Keywords:'),
			'required'      	=> true,

			// extra
			'container_class'	=> 'wide',
        ));

		$this->addElement('multiselect', 'current_availability_id', array(
            'label'         => __('What is your current availability?'),
            'required'      => true,
			'description'	=> __('Use the CTRL key to select more than one.'),
			'multioptions'  => array(
				1 => __('Part-time'),
				2 => __('Full-time (Days)'),
				3 => __('Full-time (Swing)'),
				4 => __('Full-time (Graveyard)'),
				5 => __('Weekends Only'),
			)
        ));

		$this->addElement('multiCheckbox', 'availability_id', array(
            'label'         => __('What is your current availability?'),
            'required'      => true,
			'multioptions'  => array(
				1 => __('Part-time'),
				2 => __('Full-time (Days)'),
				3 => __('Full-time (Swing)'),
				4 => __('Full-time (Graveyard)'),
				5 => __('Weekends Only'),
			)
        ));

		$this->addDisplayGroup(array(
			'info2',
			'how_contact',
			'email',
			'email_check',
			'phone',
			'fax',
			'subject',
			'message',
			'message_wide',
			'keywords',
			'current_availability_id',
			'availability_id',
		), 'contact_information');

        $this->getDisplayGroup('contact_information')->setLegend(__('Contact Information'));

		//
		// Login Information fields
		//

		// @todo
		$this->addElement('info', 'info3', array(

		));

		$this->addElement('text', 'username', array(
            'label'         => __('Username:'),
            'required'      => true,
			'description'	=> __('May only contain letters, numbers, and underscore (_) and 8-20 characters long.'),
        ));

		$this->addElement('text', 'password', array(
            'label'         => __('Password:'),
            'required'      => true,
			'description'	=> __('Must be 6-25 characters long.'),
        ));

		$this->addElement('text', 'password_check', array(
            'label'         => __('Please re-enter your password:'),
            'required'      => true,
			'description'	=> __('Must match the password you entered just above.'),
        ));

		$this->addElement('checkbox', 'remember_me', array(
            'label'         => __('Remember Me'),
            'description'	=> __("If you don't want to bother with having to login every time you visit the site, then checking \"Remember Me\" will place a unique identifier only our site can read that we'll use to identify you and log you in automatically each time you visit."),
        ));

		$this->addDisplayGroup(array(
			'info3',
			'username',
			'password',
			'password_check',
			'remember_me',
		), 'login_information');

        $this->getDisplayGroup('login_information')->setLegend(__('Login Information'));

		//
		// Verification fields
		//

		// @todo
		$this->addElement('info', 'info4', array(

		));

		$this->addDisplayGroup(array(
			'info4',
		), 'check_information');

        $this->getDisplayGroup('check_information')->setLegend(__('Verification'));


		//
		// Submits and buttons
		//

		$this->addDisplayGroup(array(
			'info4',
		), 'buttons');

        $this->getDisplayGroup('buttons');

    }
}

