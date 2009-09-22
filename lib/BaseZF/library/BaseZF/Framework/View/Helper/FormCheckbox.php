<?php
/**
 * FormCheckbox.php for BaseZF in /BaseZF/Framework/View/Helper
 *
 * @category   BaseZF_Framework
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_FormCheckbox extends Zend_View_Helper_FormCheckbox
{
    /**
     * Generates a 'checkbox' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     * @param mixed $value The element value.
     * @param array $attribs Attributes for the element tag.
     * @return string The element XHTML.
     */
    public function formCheckbox($name, $value = null, $attribs = null, array $checkedOptions = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // get label
        $label = isset($attribs['label']) ? $attribs['label'] : null;
        unset($attribs['label']);

        // retrieve attributes for labels (prefixed with 'label_' or 'label')
        $label_attribs = array('class' => '');
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

                if ($tmp == 'class') {
                    $label_attribs[$tmp] = $val . ' ' . $label_attribs[$tmp];
                } else {
                    $label_attribs[$tmp] = $val;
                }

                unset($attribs[$key]);
            }
        }

        $checked = false;
        if (isset($attribs['checked']) && $attribs['checked']) {
            $checked = true;
            unset($attribs['checked']);
        } elseif (isset($attribs['checked'])) {
            $checked = false;
            unset($attribs['checked']);
        }

        $checkedOptions = self::determineCheckboxInfo($value, $checked, $checkedOptions);

        // is the element disabled?
        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag= '>';
        }

        // build the element
        $xhtml = '';
        if (!strstr($name, '[]')) {
            //$xhtml = $this->_hidden($name, $checkedOptions['unCheckedValue']);
        }
        $xhtml .= '<label'
                . $this->_htmlAttribs($label_attribs)
                . ' for="' . $this->view->escape($name) . '">'
                . '<input type="checkbox"'
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . ' value="' . $this->view->escape($checkedOptions['checkedValue']) . '"'
                . $checkedOptions['checkedString']
                . $disabled
                . $this->_htmlAttribs($attribs)
                . $endTag
                . $this->view->escape($label)
                . '</label>';

        return $xhtml;
    }
}

