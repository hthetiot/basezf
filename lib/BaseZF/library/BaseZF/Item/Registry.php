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
 */

class BaseZF_Item_Registry
{
    static protected $_itemsInstances = array();

    public static function getItemInstance($id = null, $className)
    {
        if (is_null($id) || !$item = self::getItemExistInstance($id, $className)) {
            $item = self::createItemInstance($id, $className);
        }

        return $item;
    }

    public static function getItemExistInstance($id, $className)
    {
         if(isset(self::$_itemsInstances[$className]) &&
            array_key_exists($id, self::$_itemsInstances[$className])
         ) {
            return self::$_itemsInstances[$className][$id];
         }

         return false;
    }

    public static function createItemInstance($id, $className)
    {
        $item = new $className($id);

        return $item;
    }

    public static function saveItemInstance($item, $className)
    {
        if(!isset(self::$_itemsInstances[$className])) {
            self::$_itemsInstances[$className] = array();
        }

        self::$_itemsInstances[$className][$item->getId()] = &$item;
    }

    public static function destructItemExistInstance($item, $className)
    {
        if(isset(self::$_itemsInstances[$className]) &&
            array_key_exists($item->getId(), self::$_itemsInstances[$className])
        ) {

            unset(self::$_itemsInstances[$className][$item->getId()]);
            return true;
        }

        return false;
    }

    public static function getInstances($className)
    {
        return self::$_itemsInstances;
    }

}
