<?php
/**
 * BaseZF_Framework_View_Helper_FormInfo class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Framework_View_Helper_FormInfo extends Zend_View_Helper_FormElement
{
    public function formInfo($name, $value = null, $attribs = array(), $options = null)
    {
        // extract info and merge with existing
        $info = $this->_getInfo($name, $value, $attribs, $options);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // get label
        $label = isset($attribs['label']) ? $attribs['label'] : null;
        unset($attribs['label']);

        // get label attribs
        $labelAttribs = $this->getAttribsNamespaceValue($attribs, 'label');

        // build the element
        $xhtml = array();
        $xhtml[] = '<div' . $this->_htmlAttribs($attribs) . '>';

        // build label
        if (!empty($label)) {
            $xhtml[] = '<h4' . $this->_htmlAttribs($labelAttribs) . '>' . $this->view->escape($label) . '</h4>';
        }

        // build messages
        if (isset($options['messages'])) {

            // display message and add "last" class on last message container
            $nbMessages = count($options['messages']);
            for($i = 0; $i < $nbMessages; $i++) {
                $xhtml[] = '<p' . ($i == ($nbMessages - 1) ? ' class="last"' : '') . '>' . $options['messages'][$i] . '</p>';
            }
        }

        $xhtml[] = '</div>';

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

