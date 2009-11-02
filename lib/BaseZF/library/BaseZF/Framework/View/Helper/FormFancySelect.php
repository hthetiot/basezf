<?php
/**
 * formFancySelect.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framework
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_formFancySelect extends Zend_View_Helper_FormElement
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
        extract($info); // name, id, value, attribs, options, listsep, disable

        // get label
        $label = isset($attribs['label']) ? $attribs['label'] : null;
        unset($attribs['label']);

        // get notice
        $notice = isset($attribs['notice']) ? $attribs['notice'] : null;
        unset($attribs['notice']);

        // get notice
        $showChoice = isset($attribs['show_choice']) ? $attribs['show_choice'] : false;
        unset($attribs['show_choice']);

        // get label attribs
        $labelAttribs = $this->getAttribsNamespaceValue($attribs, 'label');

        // is multiple choice
        if (isset($attribs['multiple'])) {
            $this->_inputType = 'checkbox';
            $this->_isArray = true;
            unset($attribs['multiple']);
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
        $xhtml[] = '<div id="' . $this->view->escape($id) . '" class="formFancySelect">';
        $xhtml[] = '    <div class="formFancySelectOptions">';
        $xhtml[] = '        <div>';

        // add radio buttons to the list.
        require_once 'Zend/Filter/Alnum.php';
        $filter = new Zend_Filter_Alnum();
        foreach ($options as $optionValue => $optionLabel) {

            // Should the label be escaped?
            $optionLabel = $this->view->escape($optionLabel);

            // is it disabled?
            $disabled = '';
            if (true === $disable) {
                $disabled = ' disabled="disabled"';
            } elseif (is_array($disable) && in_array($optionValue, $disable)) {
                $disabled = ' disabled="disabled"';
            }

            // is it checked?
            $checked = '';
            if (in_array($optionValue, $value)) {
                $checked = ' checked="checked"';
            }

            // generate ID
            $optId = $id . '-' . $filter->filter($optionValue);

            // set class
            $attribs['class'] = 'form' . ucfirst($this->_inputType);

            // Wrap the radios in labels
            $radio = '          <label'
                    . $this->_htmlAttribs($labelAttribs) . '>'
                    . '<input type="' . $this->_inputType . '"'
                    . ' name="' . $name . '"'
                    . ' id="' . $optId . '"'
                    . ' value="' . $this->view->escape($optionValue) . '"'
                    . $checked
                    . $disabled
                    . $this->_htmlAttribs($attribs)
                    . $endTag
                    . '<span>' . $optionLabel . '</span>'
                    . '</label>';

            // add to the array of radio buttons
            $xhtml[] = $radio;
        }

        // add container close tag
        $xhtml[] = '        </div>';
        $xhtml[] = '    </div>';
        $xhtml[] = '</div>';

        $xhtml[] = '<div class="formFancySelectLabel">';

        if (!empty($notice)) {
            $xhtml[] = $notice;
        }

        $xhtml[] = '</div>';

        if ($showChoice) {
            $xhtml[] = '<div class="formFancySelectValue"></div>';
        }

        return implode("\n", $xhtml);
    }

    public function getAttribsNamespaceValue(array &$attribs, $namespace)
    {
         // retrieve attributes for labels (prefixed with '$namespace_' or '$namespace')
        $namespaceLen = mb_strlen($namespace);
        $namespaceAttribs = array();
        foreach ($attribs as $key => $value) {

            $namespaceKey = false;
            $keyLen = strlen($key);

            if ($keyLen <= $namespaceLen) {
                continue;
            }

            if ((substr($key, 0, $namespaceLen + 1) == $namespace . '_')) {

                $namespaceKey = strtolower(substr($key, $namespaceLen + 1));
                $namespaceAttribs[$namespaceKey] = $value;
                unset($attribs[$key]);
            }
        }
    }
}
