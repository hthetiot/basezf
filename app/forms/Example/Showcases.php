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

		$this->addElement('info', 'info1', array(
			'label'		=> __('Personal Information'),
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
            'helper'        => 'FormFancySelect',
            'label'         => __('Sexuality:'),
            'notice'        => __('Choose from the list'),
            'multioptions'  => array(
                '1' => 'Hetero',
                '2' => 'Bi',
                '3' => 'Gay'
            ),
        ));

		$this->addElement('select', 'lookingfor_id', array(
			'helper'        => 'FormFancySelect',
            'label'         => __('Her for:'),
            'multiple'      => true,
            'show_choice'   => true,
            'notice'        => __('Choose from the list'),
            'multioptions'  => array(
                '1' => 'Chatting',
                '2' => 'Promote Myself',
                '3' => 'Meeting new people',
                '4' => 'Flirting',
                '5' => 'Find the true love',
            ),
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

		$this->addElement('info', 'info2', array(
            'label'		=> __('Contact Information'),
			'messages'	=> array(
                __('Please enter your full email address, for example, name@domain.com'),
                __('It is important that you provide a valid, working email address that you have access to as it must be verified before you can use your account.'),
                __('Please enter a land line number, not a mobile phone number.'),

                // example message with HTML
                str_replace(
                    array('[link]', '[/link]'),
                    array('<a href="#todo">', '</a>'),
                    __('Your phone number will not be shared or used for telemarketing. Your information is protected by our [link]Privacy Policy[/link].')
                ),
			),
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

		$this->addElement('info', 'info3', array(
            'label'		=> __('Login Information'),
			'messages'	=> array(
                __('Your username and password must both be at least 8 characters long and are case-sensitive. Please do not enter accented characters.'),
                __('We recommend that your password is not a word you can find in the dictionary, includes both capital and lower case letters, and contains at least one special character (1-9, !, *, _, etc.).'),
                __('Your password will be encrypted and stored in our system. Due to the encryption, we cannot retrieve your password for you. If you lose or forget your password, we offer the ability to reset it.'),
            )
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

		$this->addElement('info', 'info4', array(
            'label'		=> __('Verification Information'),
			'messages'	=> array(
                __('Type the characters you see in this picture. This ensures that a person, not an automated program, is creating this account.'),
            )
		));

        // init ReCaptcha service
        $pubKey = '6Lf59QQAAAAAANrLNTVbBEt4I1TgAIwuQuc22iuN';
        $privKey = '6Lf59QQAAAAAAFaT3xLxeoIHkPNx3OFeTBcv1bXS';
        $recaptcha = new Zend_Service_ReCaptcha($pubKey, $privKey);

        // inir ReCaptcha adapter
		$adapter = new Zend_Captcha_ReCaptcha();
		$adapter->setService($recaptcha);

        // build element
        $this->addElement('captcha', 'captcha', array(
            'captcha' => $adapter,
        ));

		$this->addDisplayGroup(array(
			'info4',
            'captcha',
		), 'check_information');

        $this->getDisplayGroup('check_information')->setLegend(__('Verification'));


        // submit and reset buttons
        $this->addElement('reset', 'reset', array('label' => __('Cancel')))
             ->addElement('submit', 'update', array('label' => __('Submit')))
             ->addDisplayGroup(array('reset', 'update'), 'buttons');

        // add class for buttons
        $this->getDisplayGroup('buttons')->setAttrib('class', 'fieldsetButtons');
    }
}

