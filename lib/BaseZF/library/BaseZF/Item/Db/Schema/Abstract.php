<?php
/**
 * MyProject_Item_Db_Schema class in /BaseZF/Item/Db/Schema
 *
 * PHP version 5.2.11
 *
 * @category  BaseZF
 * @package   BaseZF_Item
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Item_Db_Schema_Abstract
{
    static protected $_schema = array();

    public static function &getTableColumns($tableName)
    {
        if (array_key_exists($tableName, self::$_schema) === false) {
            throw new BaseZF_Item_Db_Schema_Exception(sprintf('There no table "%s" in schema', $tableName));
        }

        return self::$_schema[$tableName];
    }
}
