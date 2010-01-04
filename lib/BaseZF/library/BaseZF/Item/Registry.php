<?php
/**
 * BaseZF_Item_Registry class in /BaseZF/Item
 *
 * @category   BaseZF
 * @package    BaseZF_Item, BaseZF_Collection
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *             Oleg Stephanwhite (oleg)
 *             Fabien Guiraud (fguiraud)
 *
 * @todo - limit of nb item instance via sleep and wakeup on a window
 *       - export registry for wizard feature
 */

class BaseZF_Item_Registry
{
    /**
     * Item instance indexed by item hash group by item class name
     */
    static protected $_itemsInstances = array();

    /**
     * Item hash to item id group by item class name
     */
    static protected $_itemsIdToItemHash = array();

    /**
     * Get item instance by id
     *
     * @param mixed $itemId
     * @param string $itemClassName
     *
     * @return object instance of BaseZF_Item_Abstract
     */
    public static function &getItemInstanceById($itemId, $itemClassName)
    {
        // do we have this item instance in registry indexed by item id then get it
        if(
            array_key_exists($itemClassName, self::$_itemsIdToItemHash) !== false &&
            $itemHash = array_search($itemId, self::$_itemsIdToItemHash[$itemClassName])
        ) {

            $item = &self::$_itemsInstances[$itemClassName][$itemHash];

        // else create it
        } else {
            $item = self::createItemInstanceById($itemId, $itemClassName);
        }

        return $item;
    }

    /**
     * Add item instance into registry
     *
     * @param mixed $itemId
     * @param string $itemClassName
     *
     * @return object instance of BaseZF_Item_Abstract
     */
    public static function &createItemInstanceById($itemId, $itemClassName)
    {
        $item = new $itemClassName($itemId);

        self::saveItemInstance($item, $itemClassName);

        return $item;
    }

    /**
     * Save item instance into registry
     *
     * @param object $item
     * @param string $itemClassName
     *
     * @return string item hash
     */
    public static function saveItemInstance(BaseZF_Item_Abstract $item, $itemClassName)
    {
        // get item hash
        $itemHash = self::_getItemHash($item);

        // do we have this item instance in registry indexed by item hash then save it
        if(
            array_key_exists($itemClassName, self::$_itemsInstances) === false ||
            array_key_exists($itemHash, self::$_itemsInstances[$itemClassName]) === false
        ) {
            self::$_itemsInstances[$itemClassName][$itemHash] = &$item;
        }

        // do we have this item instance in registry indexed by item id then save it
        if(array_key_exists($itemClassName, self::$_itemsIdToItemHash) == false) {
            self::$_itemsIdToItemHash[$itemClassName] = array();
        }

        self::$_itemsIdToItemHash[$itemClassName][$itemHash] = $item->getId();

        return $itemHash;
    }

    /**
     * Remove item instance from registry
     *
     * @param object $item
     * @param string $itemClassName
     *
     * @return bool true if found and removed else false
     */
    public static function removeItemInstance(BaseZF_Item_Abstract $item, $itemClassName)
    {
        $removed = false;

        // get item hash
        $itemHash = self::_getItemHash($item);

        // do we have this item instance in registry indexed by item hash then remove it
        if(
            array_key_exists($itemClassName, self::$_itemsInstances) !== false &&
            array_key_exists($itemHash, self::$_itemsInstances[$itemClassName]) !== false
        ) {
            unset(self::$_itemsInstances[$itemClassName][$itemHash]);
            unset(self::$_itemsIdToItemHash[$itemClassName][$itemHash]);
            $removed = true;
        }

        return $removed;
    }

    /**
     * Build item hash for item class instance
     *
     * @return string item instance hash
     */
    static protected function _getItemHash(BaseZF_Item_Abstract $item)
    {
        return spl_object_hash($item);
    }
}

