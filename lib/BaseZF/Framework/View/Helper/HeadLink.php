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
    static $_packsEnable = false;
    static $_prefixHref   = null;

    //
    // Prefix functions
    //

    static private function _isStylesheetItem(&$item)
    {
        return isset($item->href);
    }

    private static function _addItemHrefPrefix(&$item)
    {
        // check item has src
        if (!self::_isStylesheetItem($item)) {
            return false;
        }

        if (
            self::$_prefixHref !== null
            && substr_count($item->attributes['href'], 'http://') == 0
        ) {
            $item->attributes['href'] = self::$_prefixHref . $item->attributes['src'];
        }
    }

    public function setPrefixHref($prefix)
    {
        self::$_prefixHref = $prefix;

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

            $packFiles = array();
            $configPackFiles = glob(CONFIG_STATIC_PACK_CSS_FILES . "*.css");

            foreach ($configPackFiles as $configPackFile) {

                $packPath = str_replace(CONFIG_STATIC_PACK_CSS_FILES, CONFIG_STATIC_PACK_CSS_PATH, $configPackFile);
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
        if (!self::_isStylesheetItem($item)) {
            return false;
        }

        // search pack
        $packFiles = self::_getPacksFiles();

        // no pack found
        if (!isset($packFiles[$item->href])) {

            return $item;

        // pack found
        } else {
            $packPath = $packFiles[$item->href];
        }

        // no duplicate pack
        if (isset($packsItems[$packPath])) {
            return false;
        }

        // build item pack
        $attributes = array(
            'rel'   => 'stylesheet',
            'type'  => 'text/css',
            'media' => 'screen',
            'href'  => $packPath
        );

        $itemPack = $this->createData($attributes);
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

        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        $items = array();
        foreach ($container as $item) {

			// add prefix only if not contain "http://"
            //self::_addItemHrefPrefix($item);

            $items[] = $this->itemToString($item);
        }

        return $indent . implode($this->_escape($this->getSeparator()) . $indent, $items);
    }
}

