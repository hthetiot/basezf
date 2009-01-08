<?php
/**
 * Form class in /BazeZF/Framework
 *
 * @category   BazeZF_Framework
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
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

        $loaderElement = $this->getPluginLoader('element');
        $loaderElement->addPrefixPath('BaseZF_Framework_Form_Element', PATH_TO_INCLUDES . '/BaseZF/Framework/Form/Element/');

        $loaderDecorator = $this->getPluginLoader('decorator');
        $loaderDecorator->addPrefixPath('BaseZF_Framework_Form_Decorator', PATH_TO_INCLUDES . '/BaseZF/Framework/Form/Decorator');

	    parent::__construct($options);
	}

    /**
     * Return a json array or partial validation restults
     */
    public function processJson($formData)
    {
        return json_decode($this->processAjax($formData), true);
    }

    /**
     * Set default render
     */
	public function render($content = null)
	{
        $this->setAttrib('class', $this->getAttrib('class') . ' formLayout');

		$defaultDecorators = array(
			'FormElements',
			'Form'
		);

		$defaultGroupDecorators = array(
			'FormElements',
			'Fieldset',
		);

		$this->setElementDecorators(array('Composite'));
		$this->setDisplayGroupDecorators($defaultGroupDecorators);
		$this->setSubFormDecorators($defaultDecorators);
		$this->setDecorators($defaultDecorators);

        return parent::render($content);
	}
}

