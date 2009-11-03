<?php
/**
 * Composite class in /BazeZF/Framework/Form/Decorator
 *
 * @category   BazeZF
 * @package    BazeZF_Framework_Form
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_Form_Decorator_Composite extends Zend_Form_Decorator_Abstract
{
    static $helperWithContainerLabel = array(

        // standart helpers
        'formText',
        'formTextarea',
        'formPassword',
        'formSelect',
        'formRadio',
        'formMultiSelect',

        // some special helper
        'formDate',
        'formFancySelect',
    );

    static $helperWithoutContainer = array(
        'formInfo',
    );

    static $helpersButton = array(
        'formButton',
        'formReset',
        'formSubmit',
    );

    public function buildField()
    {
        $element = $this->getElement();
        $helper  = $element->helper;

        // update attribs to add class with helper name
        $helperAttribs = $element->getAttribs();
        $helperAttribs['class'] = trim($helper . ' ' . $element->getAttrib('class'));

        // clean buttons attribs and set label as value for button
        if (in_array($helper, self::$helpersButton) ==! false) {
            $element->setValue($element->getLabel());

        // add label and label_class for helper able to manage their own label
        } else if (in_array($helper, self::$helperWithContainerLabel) === false) {
            $helperAttribs['label_class'] = trim('formLabel' . ucfirst(str_replace('form', '', $helper)) . ' ' . $element->getAttrib('label_class'));
            $helperAttribs['label'] = $element->getLabel();
        }

        // remove useless attribs for helper
        unset($helperAttribs['helper']);
        unset($helperAttribs['container_class']);

        return $element->getView()->$helper(
            $element->getName(),
            $element->getValue(),
            $helperAttribs,
            $element->options
        );
    }

    public function buildDescription()
    {
        $desc = $this->getElement()->getDescription();

        if (empty($desc)) {
            return;
        }

        return '<small>' . $desc . '</small>';
    }

    public function buildLabel()
    {
        $element = $this->getElement();
        $helper  = $element->helper;

        // ignore label for helper able to manage their own label
        if (in_array($helper, self::$helperWithContainerLabel) === false) {
            return;
        }

        // get it !
        $label = $element->getLabel();

        // translate it ?
        if ($translator = $element->getTranslator()) {
            $label = $translator->translate($label);
        }

        $attribs = array(
            'class' => 'formLabel' . ucfirst(str_replace('form', '', $helper)),
        );

        return $element->getView()->formLabel($element->getName(), $label, $attribs);
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
        if (in_array($helper, self::$helperWithoutContainer) ==! false) {
            return null;
        }

        // render containerClass
        $containerClass = array();

        if ($element->container_class) {
            $containerClass = explode(' ', $element->container_class);
        }

        // add default class
        if (in_array($helper, self::$helpersButton) ==! false) {
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
        $view = $element->getView();

        // ignore special elements
        if (
            $element instanceof Zend_Form_Element_Captcha ||
            !$element instanceof Zend_Form_Element ||
            !$view instanceof Zend_View_Interface
        ) {
            return $content;
        }


        // render container
        $containerClass = $this->getContainerClass();
        $output = '<div ' . ($containerClass ? 'class="' . $containerClass . '"' : '') . '>'
                . $this->buildErrors()
                . $this->buildLabel()
                . $this->buildField()
                . $this->buildDescription()
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
