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
        $this->setAttrib('class', 'formAjaxValidate');

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
			'label'		    => __('Birthday:'),
            'required'      => true,
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
            'required'      => true,
            'multioptions'  => array(
                '1' => __('Hetero'),
                '2' => __('Bi'),
                '3' => __('Gay')
            ),
        ));

		$this->addElement('multiselect', 'lookingfor_id', array(
			'helper'        => 'FormFancySelect',
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
                sprintf(
                    __('Your phone number will not be shared or used for telemarketing. Your information is protected by our %s Privacy Policy %s.'),
					'<a href="#todo">', '</a>'
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

        $this->getElement('email')->addValidator('EmailAddress', true, array(
            'messages' => array(
                'emailAddressInvalid'           => __('This does not appear to be a valid email address'),
                'emailAddressInvalidHostname'   => __('This does not appear to be a valid email address'),
                'emailAddressInvalidMxRecord'   => __('This does not appear to be a valid email address'),
                'emailAddressDotAtom'           => __('This does not appear to be a valid email address'),
                'emailAddressQuotedString'      => __('This does not appear to be a valid email address'),
                'emailAddressInvalidLocalPart'  => __('This does not appear to be a valid email address'),
        )));

		$this->addElement('text', 'email_check', array(
            'label'         => __('Re-enter Email:'),
            'required'      => true,
			'description'	=> __('Must match the email address you just entered above.'),
        ));

        $this->getElement('email_check')->addValidator('StringEquals', true, array(
            'field1' => 'email',
            'field2' => 'email_check',
            'messages' => array(
                'notMatch'  => __("Emails don't match, please enter them again")
            ),
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
			'description'	 => __('May only contain letters, numbers, and underscore (_) and 8-20 characters long.'),
        ));

		$this->addElement('password', 'password', array(
            'label'         => __('Password:'),
            'required'      => true,
			'description'	 => __('Must be 6-25 characters long.'),
        ));

		$this->addElement('password', 'password_check', array(
            'label'         => __('Please re-enter your password:'),
            'required'      => true,
			'description'	 => __('Must match the password you entered just above.'),
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
            'description'	=> __("If you don't want to bother with having to login every time you visit the site, then checking \"Remember Me\" will place a unique identifier only our site can read that we'll use to identify you and log you in automatically each time you visit."),
        ));

        $this->addElement('checkbox', 'therms', array(
            'label'         => __('I agree to the bellow terms'),
            'required'      => true,
            'description'	 => sprintf(
                __('By checking this, you are indicating that you are agree with the %s Terms of Use %s.'),
                '<a href="#">', '</a>'
            )
        ));

        $thermsRequired = new Zend_Validate_InArray(array(1));
        $thermsRequired->setMessage('You should accept the Terms of Use');
        $this->getElement('therms')->addValidator($thermsRequired);


		$this->addDisplayGroup(array(
			'info3',
			'username',
			'password',
			'password_check',
			'remember_me',
            'therms',
		), 'login_information');

        $this->getDisplayGroup('login_information')->setLegend(__('Login Information'));

        //
		// Avatar
		//

        $this->addElement('info', 'info5', array(
            'label'		=> __('Avatar Information'),
			'messages'	=> array(
                __('You can upload a JPG, GIF or PNG file.'),
                __('File size limit 4 MB. If your upload does not work, try a smaller picture.'),
            )
		));

        // add synchron upload support
        /*
        $this->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);
        $this->addElement('file', 'avatar_file', array(
            'label'         => __('Upload your Avatar:'),
            'required'      => true,

			// extras
			'container_class'	=> 'wide',
        ));
*/
        $this->addElement('checkbox', 'therms_file', array(
            'label'         => __('I certify that I have the right to distribute this picture and that it does not violate the Terms of Use'),
            'required'      => true,

            // extras
			'container_class'	=> 'wide',
        ));

        $thermsRequired = new Zend_Validate_InArray(array(1));
        $thermsRequired->setMessage('You should accept the Terms of Use');
        $this->getElement('therms_file')->addValidator($thermsRequired);

        $this->addDisplayGroup(array(
			'info5',
            'avatar_file',
            'therms_file',
		), 'avatar');

        $this->getDisplayGroup('avatar')->setLegend(__('Avatar'));

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

