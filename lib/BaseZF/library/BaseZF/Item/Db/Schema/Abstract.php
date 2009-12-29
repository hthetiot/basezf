<?php
/**
 * MyProject_Item_Db_Schema class in /BaseZF/Item/Db/Schema
 *
 * @category   BaseZF
 * @package    BaseZF_Item, BaseZF_Collection
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *             Oleg Stephanwhite (oleg)
 *             Fabien Guiraud (fguiraud)
 */

class BaseZF_Item_Db_Schema_Abstract
{
    static protected $_schema = array();

    public static function &getTableStructure($tableName)
    {
        if(!array_key_exists($tableName, self::$_schema)) {
            throw new BaseZF_Item_Db_Schema_Exception(sprintf('There no table "%s" in schema', $tableName));
        }

        return self::$_schema[$tableName];
    }
}
