<?php
/**
 * HeadLink.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_HeadLink extends Zend_View_Helper_HeadLink
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
     * Href prefix to use CDN server or other host for static content
     */
    static $_prefixHref;

    //
    // Prefix functions
    //

    private static function _addItemHrefPrefix(&$item)
    {
        // add preffix for cdn if required
        if (
            isset($item->href) &&
            isset(self::$_prefixHref) &&
            substr_count($item->attributes['href'], 'http://') == 0
        )
        {
            $item->attributes['href'] = self::$_prefixHref . $item->attributes['href'];
        }
    }

    public function setPrefixHref($prefixHref)
    {
        self::$_prefixHref = $prefixHref;

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
        if (!isset($item->href)) {
            return false;
        }

        // search pack
        foreach (self::$_packsConfig as $packPath => $items) {

            if(in_array($item->href, $items)) {
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
        $attributes = array(
            'rel'   => 'stylesheet',
            'type'  => 'text/css',
            'media' => 'screen',
            'href'  => $matchPackPath
        );

        $itemPack = $this->createData($attributes);
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
        // looking for packs
        $items = array();
        foreach ($this as $item) {
            if($itemPack = $this->_getItemPack($item)) {
                $items[] = $this->itemToString($itemPack);
            }
        }

        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        return $indent . implode($this->_escape($this->getSeparator()) . $indent, $items);
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

