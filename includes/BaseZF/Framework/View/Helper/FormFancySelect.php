<?php
/**
 * FormFancySelect.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_FormFancySelect extends Zend_View_Helper_FormElement
{
    /**
     * Input type to use
     * @var string
     */
    protected $_inputType = 'radio';

    /**
     * Whether or not this element represents an array collection by default
     * @var bool
     */
    protected $_isArray = false;

    public function formFancySelect($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, value, attribs, options, listsep, disable

        // retrieve attributes for labels (prefixed with 'label_' or 'label')
        $label_attribs = array();
        foreach ($attribs as $key => $val) {
            $tmp    = false;
            $keyLen = strlen($key);
            if ((6 < $keyLen) && (substr($key, 0, 6) == 'label_')) {
                $tmp = substr($key, 6);
            } elseif ((5 < $keyLen) && (substr($key, 0, 5) == 'label')) {
                $tmp = substr($key, 5);
            }

            if ($tmp) {
                // make sure first char is lowercase
                $tmp[0] = strtolower($tmp[0]);
                $label_attribs[$tmp] = $val;
                unset($attribs[$key]);
            }
        }

		// add class formLabelRadio to label per default
		$label_attribs['class'] = (isset($label_attribs['class']) ? $label_attribs['class'] . ' ' : '') . 'formLabelRadio';

        $labelPlacement = 'append';
        foreach ($label_attribs as $key => $val) {
            switch (strtolower($key)) {
                case 'placement':
                    unset($label_attribs[$key]);
                    $val = strtolower($val);
                    if (in_array($val, array('prepend', 'append'))) {
                        $labelPlacement = $val;
                    }
                    break;
            }
        }

        if (isset($attribs['multiple'])) {
            $this->_inputType = 'checkbox';
            $this->_isArray = true;
        }

        // the radio button values and labels
        $options = (array) $options;

        // build the element
        $xhtml = array();

        // should the name affect an array collection?
        $name = $this->view->escape($name);
        if ($this->_isArray && ('[]' != substr($name, -2))) {
            $name .= '[]';
        }

        // ensure value is an array to allow matching multiple times
        $value = (array) $value;

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag= '>';
        }

        // add container open tag
        $xhtml[] = '<div class="formFancySelect">';
        $xhtml[] = '<div class="formFancySelectOptions">';
        $xhtml[] = '<div>';

        // add radio buttons to the list.
        require_once 'Zend/Filter/Alnum.php';
        $filter = new Zend_Filter_Alnum();
        foreach ($options as $opt_value => $opt_label) {

            // Should the label be escaped?
            if ($escape) {
                $opt_label = $this->view->escape($opt_label);
            }

            // is it disabled?
            $disabled = '';
            if (true === $disable) {
                $disabled = ' disabled="disabled"';
            } elseif (is_array($disable) && in_array($opt_value, $disable)) {
                $disabled = ' disabled="disabled"';
            }

            // is it checked?
            $checked = '';
            if (in_array($opt_value, $value)) {
                $checked = ' checked="checked"';
            }

            // generate ID
            $optId = $id . '-' . $filter->filter($opt_value);

            // set class
            $attribs['class'] = 'form' . ucfirst($this->_inputType);

            // Wrap the radios in labels
            $radio = '<label'
                    . $this->_htmlAttribs($label_attribs) . '>'
                    . (('prepend' == $labelPlacement) ? '<span>' . $opt_label . '</span>' : '')
                    . '<input type="' . $this->_inputType . '"'
                    . ' name="' . $name . '"'
                    . ' id="' . $optId . '"'
                    . ' value="' . $this->view->escape($opt_value) . '"'
                    . $checked
                    . $disabled
                    . $this->_htmlAttribs($attribs)
                    . $endTag
                    . (('append' == $labelPlacement) ? '<span>' . $opt_label . '</span>' : '')
                    . '</label>';

            // add to the array of radio buttons
            $xhtml[] = $radio;
        }

        // add container close tag
        $xhtml[] = '</div>';
        $xhtml[] = '</div>';
        $xhtml[] = '</div>';

        $xhtml[] = '<div class="formFancySelectLabel">';

        if (isset($attribs['notice'])) {
            $xhtml[] = $attribs['notice'];
        }

        $xhtml[] = '</div>';

        if (isset($attribs['show_choice'])) {
            $xhtml[] = '<div class="formFancySelectValue"></div>';
        }

        return implode("\n", $xhtml);
    }
}
