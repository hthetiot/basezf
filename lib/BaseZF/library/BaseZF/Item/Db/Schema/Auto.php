<?php
/**
 * BaseZF_Item_Db_Schema_Auto class in /BaseZF/Item/Db/Schema
 *
 * @category   BaseZF
 * @package    BaseZF_Item, BaseZF_Collection
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *             Oleg Stephanwhite (oleg)
 *             Fabien Guiraud (fguiraud)
 */

class BaseZF_Item_Db_Schema_Auto extends BaseZF_Item_Db_Schema_Abstract
{
    static protected $_schema = array();

    static public function loadSchemaFromDb(Zend_Db_Adapter_Abstract $db)
    {
        $tables = $db->listTables();

        foreach ($tables as $table) {
            self::$_schema[$table] = $db->describeTable($table);
        }
    }

    public static function &getTableStructure($tableName)
    {

        if(!array_key_exists($tableName, self::$_schema)) {
            throw new BaseZF_Item_Db_Schema_Exception(sprintf('There no table "%s" in schema', $tableName));
        }

        return self::$_schema[$tableName];
    }
}
