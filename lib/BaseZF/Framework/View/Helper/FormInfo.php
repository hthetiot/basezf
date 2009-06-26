<?php
/**
 * FormInfo.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_FormInfo extends Zend_View_Helper_FormElement
{
    public function formInfo($name, $value = null, $attribs = array(), $options = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // get label
        $label = isset($attribs['label']) ? $attribs['label'] : null;
        unset($attribs['label']);

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

        // build the element
        $xhtml = array();
        $xhtml[] = '<div' . $this->_htmlAttribs($attribs) . '>';

        // build label
        if (!empty($label)) {
            $xhtml[] = '<h4' . $this->_htmlAttribs($label_attribs) . '>' . $this->view->escape($label) . '</h4>';
        }

        // build messages
        if (isset($attribs['messages'])) {

            $messages = $attribs['messages'];

            // set has array
            if (!is_array($messages)) {
                $messages = array($messages);
            }

            $nbMessages = count($messages);
            for($i = 0; $i < $nbMessages; $i++) {

                $xhtml[] = '<p' . ($i == ($nbMessages - 1) ? ' class="last"' : '') . '>' . $messages[$i] . '</p>';
            }

        }

        $xhtml[] = '</div>';

        return implode("\n", $xhtml);
    }
}

