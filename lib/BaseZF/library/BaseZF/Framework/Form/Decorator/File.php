<?php
/**
 * File class in /BazeZF/Framework/Form/Decorator
 *
 * @category   BazeZF
 * @package    BazeZF_Framework_Form
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_Form_Decorator_File
    extends BaseZF_Framework_Form_Decorator_Composite
    implements Zend_Form_Decorator_Marker_File_Interface
{
    /**
     * Attributes that should not be passed to helper
     * @var array
     */
    protected $_attribBlacklist = array('helper', 'placement', 'separator', 'value');

    /**
     * Default placement: append
     * @var string
     */
    protected $_placement = 'APPEND';

    /**
     * Get attributes to pass to file helper
     *
     * @return array
     */
    public function getAttribs()
    {
        $attribs = $this->getOptions();

        if (null !== ($element = $this->getElement())) {
            $attribs = array_merge($attribs, $element->getAttribs());
        }

        foreach ($this->_attribBlacklist as $key) {
            if (array_key_exists($key, $attribs)) {
                unset($attribs[$key]);
            }
        }

        return $attribs;
    }

    public function buildField()
    {
        $element = $this->getElement();
        $name = $element->getName();
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

        // render field
        $markup = array();
        $view = $element->getView();
        $separator = $this->getSeparator();

        // has max file size
        $size = $element->getMaxFileSize();
        if ($size > 0) {
            $element->setMaxFileSize(0);
            $markup[] = $view->formHidden('MAX_FILE_SIZE', $size);
        }

        // manage multiple element
        if ($element->isArray()) {
            $name .= "[]";
            $count = $element->getMultiFile();
            for ($i = 0; $i < $count; ++$i) {
                $htmlAttribs        = $helperAttribs;
                $htmlAttribs['id'] .= '-' . $i;
                $markup[] = $view->formFile($name, $htmlAttribs);
            }
        } else {
            $markup[] = $view->formFile($name, $helperAttribs);
        }

        $markup = implode($separator, $markup);

        return $markup;
    }
}

