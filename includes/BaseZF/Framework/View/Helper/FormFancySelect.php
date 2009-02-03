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
    public function formFancySelect($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // force $value to array so we can compare multiple values to multiple
        // options; also ensure it's a string for comparison purposes.
        $value = array_map('strval', (array) $value);

        // check if element may have multiple values
        $multiple = '';

        if (substr($name, -2) == '[]') {
            // multiple implied by the name
            $multiple = ' multiple="multiple"';
        }

        $inputType = 'radio';
        if (isset($attribs['multiple'])) {

            $inputType = 'checkbox';

            // Attribute set
            if ($attribs['multiple']) {
                // True attribute; set multiple attribute
                $multiple = ' multiple="multiple"';

                // Make sure name indicates multiple values are allowed
                if (!empty($multiple) && (substr($name, -2) != '[]')) {
                    $name .= '[]';
                }
            } else {
                // False attribute; ensure attribute not set
                $multiple = '';
            }
            unset($attribs['multiple']);
        }

        // now start building the XHTML.
        $disabled = '';
        if (true === $disable) {
            $disabled = ' disabled="disabled"';
        }

        $xhtml = array();
        $xhtml[] = '<div class="formFancySelect">';
        $xhtml[] = '<div class="formFancySelectOptions">';
        $xhtml[] = '<div>';

        $i = 0;
        foreach ((array) $options as $opt_value => $opt_label) {

            $optiondId = $name . '_' . $i;

            $xhtml[] = '<label for="' . $optiondId . '">
                            <input class="form' . ucfirst($inputType) . '" type="' . $inputType . '" name="' . $name . '" value="' . $this->view->escape($opt_value) . '" id="' . $optiondId . '"/>
                            <span>' . $this->view->escape($opt_label) . '</span>
                        </label>';
            $i++;
        }

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
