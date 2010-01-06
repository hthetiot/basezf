<?php
/**
 * BaseZF_Framework_View_Helper_HeadScript class in /BaseZF/Framework/View/Helper
 *
 * @category  BaseZF
 * @package   BaseZF_Framework
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Framework_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    /**
     * Enable pack
     */
    static $_packsEnable = false;

    /**
     * Pack config
     */
    static $_packsConfig = array();

    /**
     * Src prefix to use CDN server or other host for static content
     */
    static $_prefixSrc;

    //
    // Prefix functions
    //

    private static function _addItemSrcPrefix(&$item)
    {
        // add preffix for cdn if required
        if (
            isset($item->src) &&
            isset(self::$_prefixSrc) &&
            substr_count($item->attributes['src'], 'http://') == 0
        )
        {
            $item->attributes['src'] = self::$_prefixSrc . $item->attributes['src'];
        }
    }

    public function setPrefixSrc($prefix)
    {
        self::$_prefixSrc = $prefix;

        return $this;
    }

    //
    // Pack functions
    //

    public function enablePacks($packsEnable = true)
    {
        self::$_packsEnable = $packsEnable;

        return $this;
    }

    public function setPacksConfig(array $packsConfig)
    {
        self::$_packsConfig = $packsConfig;

        return $this;
    }

    private function _getItemPack(&$item)
    {
        static $packsItems = array();

        // check item has href then no pack possible
        if (!isset($item->attributes['src'])) {
            return false;
        }



        // search pack
        foreach (self::$_packsConfig as $packPath => $items) {

            if (in_array($item->attributes['src'], $items)) {
                $matchPackPath = $packPath;
            }
        }

        // no pack found
        if (!isset($matchPackPath)) {
            return $item;
        }

        // no duplicate pack
        if (isset($packsItems[$matchPackPath])) {
            return false;
        }

        // build item pack
        $itemPack = $this->createData('text/javascript', array('src' => $matchPackPath));
        $packsItems[$matchPackPath] = &$itemPack;

        return $itemPack;
    }

    /**
     * Retrieve string representation
     *
     * @param  string|int $indent
     * @return string
     */
    public function toStringPacked($indent = null)
    {
        // escape options
        if ($this->view) {
            $useCdata = $this->view->doctype()->isXhtml() ? true : false;
        } else {
            $useCdata = $this->useCdata ? true : false;
        }
        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>'       : '//-->';

        // indent options
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        // looking for packs
        $items = array();
        foreach ($this as $item) {
            if ($itemPack = $this->_getItemPack($item)) {
                $items[] = $this->itemToString($itemPack, $indent, $escapeStart, $escapeEnd);
            }
        }

        return implode($this->getSeparator(), $items);
    }

    /**
     * Retrieve string representation
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        // if static pack enable use toStringPacked instead of toString
        if (self::$_packsEnable) {
            return $this->toStringPacked($indent);
        } else {
            return parent::toString($indent);
        }
    }
}

