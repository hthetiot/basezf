<?php
/**
 * BaseZF_Framework_View_Helper_FormFile class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Framework_View_Helper_FormFile extends Zend_View_Helper_FormFile
{
    /**
     * Generates a 'file' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formFile($name, $attribs = null)
    {
        $info = $this->_getInfo($name, null, $attribs);
        extract($info); // name, id, value, attribs, options, listsep, disable

        // is it disabled?
        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        // get label
        $label = isset($attribs['label']) ? $attribs['label'] : null;
        unset($attribs['label']);

        // get label attribs
        $labelAttribs = $this->getAttribsNamespaceValue($attribs, 'label');

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag= '>';
        }

        $xhtml = array();

        $xhtml[] = '<div class="formFileContainer">';

        // build the upload identifier
        $xhtml[] = '    <input type="hidden" name="' . $this->view->escape($name) . '_id" id="' . $this->view->escape($id) . '_id" value="' . md5(uniqid(rand())) . '" />';

        // build the element
        $xhtml[] = '    <input type="file"'
                 . ' onmouseout="this.onchange" onchange="$(\'' . $this->view->escape($id) . '_fake\').value = this.value"'
                 . ' name="' . $this->view->escape($name) . '"'
                 . ' id="' . $this->view->escape($id) . '"'
                 . $disabled
                 . $this->_htmlAttribs($attribs)
                 . $endTag;
        // fake
        $xhtml[] = '    <div>';
        $xhtml[] = '        <input class="formFileFake formText" type="text" id="' . $this->view->escape($id) . '_fake" />';
        $xhtml[] = '        <input class="formButton" type="button" id="' . $this->view->escape($id) . '_button_fake" value="' . $this->view->escape(__('Browse')) . '" />';
        $xhtml[] = '    </div>';

        $xhtml[] = '</div>';

        $xhtml[] = '<div class="progressbar"></div>';


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

