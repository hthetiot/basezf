<?php
/**
 * BaseZF_Item_Db_Schema_Auto class in /BaseZF/Item/Db/Schema
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

class BaseZF_Item_Db_Schema_Auto extends BaseZF_Item_Db_Schema_Abstract
{
    static public $schema = array();

    static public function loadSchemaFromDb(Zend_Db_Adapter_Abstract $db, Zend_Cache_Core $cache)
    {
        static $tables = array();

        if (empty($tables)) {
            if (!$tables = $cache->load('tables')) {
                $tables = $db->listTables();
                $cache->save( $tables, 'tables');
            }
        }

        foreach ($tables as $table) {

            if (!isset(self::$schema[$table])) {

                if (!$tableStructure = $cache->load('tables_' . $table)) {
                    $tableStructure = $db->describeTable($table);
                    $cache->save($tableStructure, 'tables_' . $table);
                }

                self::$schema[$table] = $tableStructure;
            }
        }


    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME => string; name of database or schema
     * TABLE_NAME  => string;
     * COLUMN_NAME => string; column name
     * COLUMN_POSITION => number; ordinal position of column in table
     * DATA_TYPE   => string; SQL datatype name of column
     * DEFAULT     => string; default expression of column, null if none
     * NULLABLE    => boolean; true if column can have nulls
     * LENGTH      => number; length of CHAR/VARCHAR
     * SCALE       => number; scale of NUMERIC/DECIMAL
     * PRECISION   => number; precision of NUMERIC/DECIMAL
     * UNSIGNED    => boolean; unsigned property of an integer type
     * PRIMARY     => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public static function &getTableColumns($tableName, $schemaName = null)
    {
        if (!array_key_exists($tableName, self::$schema)) {
            throw new BaseZF_Item_Db_Schema_Exception(sprintf('There no table "%s" in schema', $tableName));
        }

        return self::$schema[$tableName];
    }
}
