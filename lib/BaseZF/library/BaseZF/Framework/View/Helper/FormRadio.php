<?php
/**
 * FormRadio.php for BaseZF in /BaseZF/Framework/View/Helper
 *
 * @category   BaseZF_Framework
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_FormRadio extends Zend_View_Helper_FormRadio
{
    /**
     * Generates a set of radio button elements.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The radio value to mark as 'checked'.
     *
     * @param array $options An array of key-value pairs where the array
     * key is the radio value, and the array value is the radio text.
     *
     * @param array|string $attribs Attributes added to each radio.
     *
     * @return string The radio buttons XHTML.
     */
    public function formRadio($name, $value = null, $attribs = null,
        $options = null, $listsep = "\n")
    {
        return parent::formRadio($name, $value, $attribs, $options, $listsep);
    }
}
