<?php
/**
 * BaseZF_Framework_View_Helper_FormMultiRadio class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Framework_View_Helper_FormMultiRadio extends Zend_View_Helper_FormRadio
{
    /**
     * Whether or not this element represents an array collection by default
     * @var bool
     */
    protected $_isArray = false;

    /**
     * Generates a set of checkbox button elements.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The checkbox value to mark as 'checked'.
     *
     * @param array $options An array of key-value pairs where the array
     * key is the checkbox value, and the array value is the radio text.
     *
     * @param array|string $attribs Attributes added to each radio.
     *
     * @return string The radio buttons XHTML.
     */
    public function formMultiRadio($name, $value = null, $attribs = null,
        $options = null, $listsep = "\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // get label
        $label = isset($attribs['label']) ? $attribs['label'] : null;
        unset($attribs['label']);

        // update classnames
        if (isset($attribs['class'])) {
            $attribs['class'] = str_replace('Multi', '', $attribs['class']);
        }

        // update label classname
        if (isset($attribs['label_class'])) {
            $attribs['label_class'] = str_replace('Multi', '', $attribs['label_class']);
        }

        $xhtml = '<fieldset id="' . $this->view->escape($id) . '">'
               . ($label ==! null ? '<legend>' . $this->view->escape(trim($label)) . '</legend>' : null)
               . $this->formRadio($name, $value, $attribs, $options, $listsep)
               . '</fieldset>';

        return $xhtml;
    }
}

