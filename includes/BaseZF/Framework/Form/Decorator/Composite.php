<?php
/**
 * Form class in /BazeZF/Framework
 *
 * @category   BazeZF_Framework
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_Form_Decorator_Composite extends Zend_Form_Decorator_Abstract
{
    static $helperWithoutLabel = array(
		'formInfo',
        'formCheckbox',
		'formMultiCheckbox',
		'formMultiRadio',
        'formReset',
		'formSubmit',
	);

	static $helperWithoutContainerClass = array(
		'formInfo',
	);

    static $helpersButton = array(
		'formReset',
		'formSubmit',
	);

    public function buildField()
    {
        $element = $this->getElement();
		$helper  = $element->helper;

		// update attribs : remove helper attribute and merge helper name with class
		$newAttribs = $element->getAttribs();
		$newAttribs['class'] = $helper . ' ' . $element->getAttrib('class');

		// do not display useless label
		if(in_array($helper, self::$helperWithoutLabel) ==! false) {

			$labelClass = 'formLabel' . ucfirst(str_replace('form', '', $helper));

			$newAttribs['label'] = $element->getLabel();
			$newAttribs['label_class'] = $labelClass . ' ' . $element->getAttrib('label_class');
		}

        // set label has value for buttons
        if(in_array($helper, self::$helpersButton) ==! false) {
			$element->setValue($element->getLabel());
		}

		// clean attribs used by current decorator
		unset($newAttribs['helper']);
		unset($newAttribs['container_class']);

        return $element->getView()->$helper(
            $element->getName(),
            $element->getValue(),
            $newAttribs,
            $element->options
        );
    }

    public function buildDescription()
    {
        $desc = $this->getElement()->getDescription();

        if (empty($desc)) {
            return '';
        }

        return '<small>' . $desc . '</small>';
    }

    public function buildLabel()
    {
        $element = $this->getElement();
		$helper  = $element->helper;
        $label = $element->getLabel();

		// do not display useless label
		if(in_array($helper, self::$helperWithoutLabel) ==! false) {
			$element->setAttrib('label', $label);
			return '';
		}

        // translate it ?
        if ($translator = $element->getTranslator()) {
            $label = $translator->translate($label);
        }

        return $element->getView()->formLabel($element->getName(), $label);
    }

    public function buildErrors()
    {
        $element  = $this->getElement();

        if (!$element->hasErrors()) {
            return;
        }

        $messages = $element->getMessages();

        // translate it ?
        if ($translator = $element->getTranslator()) {
            foreach ($messages as $key => $value) {
                $messages[$key] = $translator->translate($value);
            }
        }

        return $element->getView()->formErrors($messages);
    }

    public function getContainerClass()
    {
        $element = $this->getElement();
		$helper  = $element->helper;

		// do not display useless container
		if(in_array($helper, self::$helperWithoutContainerClass) ==! false) {
			return '';
		}

        // render containerClass
        $containerClass = array();

        if ($element->container_class) {
            $containerClass = explode(' ', $element->container_class);
        }

        // add default class
        if(in_array($helper, self::$helpersButton) ==! false) {
			$containerClass[] = 'inline';
		} else {
            $containerClass[] = ($element->isRequired() ? 'required' : 'optional');
        }

        // add errors class
        if ($element->hasErrors()) {
            $containerClass[] = 'error';
        }

        return (!empty($containerClass) ? implode(' ', $containerClass) : '');
    }

    public function render($content)
    {
        $element = $this->getElement();

        // ignore special elements
        if (
            !$element instanceof Zend_Form_Element ||
            $element instanceof Zend_Form_Element_Captcha
        ) {
            return $content;
        }

        if (null === $element->getView()) {
            return $content;
        }

        // render sub parts
        $label     = $this->buildLabel();
        $field     = $this->buildField();
        $errors    = $this->buildErrors();
        $desc      = $this->buildDescription();

        // render container
        $containerClass = $this->getContainerClass();
        $output = '<div ' . ($containerClass ? 'class="' . $containerClass . '"' : '') . '>'
                . $errors
                . $label
                . $field
                . $desc
                . '</div>';

        // render
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();

        switch ($placement) {
            case (self::PREPEND):
                return $output . $separator . $content;
            case (self::APPEND):
            default:
                return $content . $separator . $output;
        }
    }
}
