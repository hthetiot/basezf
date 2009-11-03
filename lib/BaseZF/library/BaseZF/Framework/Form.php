<?php
/**
 * Form class in /BazeZF/Framework
 *
 * @category   BazeZF
 * @package    BazeZF_Framework_Form
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

abstract class BaseZF_Framework_Form extends Zend_Form
{
    /**
     * Constructor
     *
     * Registers form view helper as decorator
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        $this->setDisableTranslator(true);

        // set default options
        $defaultOptions = array(

            'elementPrefixPath' => array(
                array(
                    'prefix'    => 'BaseZF_Framework',
                    'path'      =>'BaseZF/Framework'
                ),
            ),

            'prefixPath' => array(

                'element' => array(
                    'prefix'    => 'BaseZF_Framework_Form_Element',
                    'path'      =>'BaseZF/Framework/Form/Element'
                ),

                'decorator' => array(
                    'prefix'    => 'BaseZF_Framework_Form_Decorator',
                    'path'      =>'BaseZF/Framework/Form/Decorator'
                ),
            ),

            'elementDecorators' => array(
                'Composite'
            ),

            'displayGroupDecorators' => array(
                'FormElements',
                'Fieldset',
            ),

            'decorators' => array(
                'FormElements',
                'Form'
            ),
        );

        $options = is_array($options) ? $options : array();
        $options = array_merge($defaultOptions, $options);

        parent::__construct($options);

        // avoid bad options setting
		$this->setDisplayGroupDecorators($options['displayGroupDecorators']);

        // add default form class
        $this->setAttrib('class', trim($this->getAttrib('class') . ' formLayout'));
    }

    /**
     * Return a json array or partial validation restults
     */
    public function processJson($formData)
    {
        $response = array();

        if (!$this->isValidPartial($formData)) {
            $response = $this->getMessages();
        }

        // translate it
        foreach ($response as $params => $messages) {

            $translatedMessages = array();
            foreach ($messages as $error => $message) {
                $translatedMessages[$error] = __($message, 'validate');
            }

            $response[$params] = $translatedMessages;
        }

        $params = array_keys($formData);
        foreach ($params as $param) {
            if (!isset($response[$param])) {
                $response[$param] = null;
            }
        }

        return $response;
    }
}

