<?php
/**
 * HeadScript.php
 *
 * @category   BaseZF
 * @package    BaseZF_Framwork
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Framework_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    static $_packsEnable = false;
    static $_prefixSrc   = null;

    //
    // Prefix functions
    //

    static private function _itemHasAttributeSrc(&$item)
    {
        return isset($item->attributes['src']);
    }

    private static function _addItemSrcPrefix(&$item)
    {
        // check item has src
        if (!self::_itemHasAttributeSrc($item)) {
            return false;
        }

        if (
            self::$_prefixSrc !== null
            && substr_count($item->attributes['src'], 'http://') == 0
        ) {
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

    public function enablePacks($enable)
    {
        self::$_packsEnable = (boolean) $enable;

        return $this;
    }

    static private function _getPacksFiles()
    {
        static $packFiles = array();

        if (empty($packFiles)) {

            $configPackFiles = glob(CONFIG_STATIC_PACK_JS_FILES . "*.js");

            foreach ($configPackFiles as $configPackFile) {

                $packPath = str_replace(CONFIG_STATIC_PACK_JS_FILES, CONFIG_STATIC_PACK_JS_PATH, $configPackFile);
                $packedFiles = explode("\n", trim(file_get_contents($configPackFile)));

                foreach ($packedFiles as $packedFile) {

                    // ignore empty and comment
                    if (
                        mb_strlen($packedFile) == 0
                        || substr($packedFile, 0, 1) == '#'
                    ) {
                        continue;
                    }

                    $packFiles[$packedFile] = $packPath;
                }
            }
        }

        return $packFiles;
    }

    private function _getItemPack(&$item)
    {
        static $packsItems = array();

        // check item has src
        if (!self::_itemHasAttributeSrc($item)) {
            return false;
        }

        // search pack
        $packFiles = self::_getPacksFiles();

        // no pack found
        if (!isset($packFiles[$item->attributes['src']])) {

            return $item;

        // pack found
        } else {
            $packPath = $packFiles[$item->attributes['src']];
        }

        // no duplicate pack
        if (isset($packsItems[$packPath])) {
            return false;
        }

        // build item pack
        $itemPack = $this->createData('text/javascript', array('src' => $packPath));
        $packsItems[$packPath] = &$itemPack;

        return $itemPack;
    }

    /**
     * Retrieve string representation
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        // if no static pack
        if (self::$_packsEnable == false) {
            return parent::toString($indent);
        }

        // looking for packs
        $container = array();
        foreach ($this as $item) {

            $itemPack = $this->_getItemPack($item);

            if (is_object($itemPack)) {
                $container[] = $itemPack;
            }
        }

        // display items
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->doctype()->isXhtml() ? true : false;
        } else {
            $useCdata = $this->useCdata ? true : false;
        }
        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>'       : '//-->';

        $items = array();
        foreach ($container as $item) {

            if (!$this->_isValid($item)) {
                continue;
            }

            // add prefix only if not contain "http://"
            self::_addItemSrcPrefix($item);

            $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
        }

        $return = implode($this->getSeparator(), $items);
        return $return;
    }
}

