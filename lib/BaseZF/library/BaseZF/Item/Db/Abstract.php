<?php
/**
 * BaseZF_Item_Db_Abstract class in /BaseZF/Item/Db
 *
 * @category   BaseZF
 * @package    BaseZF_Item, BaseZF_Collection
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *             Oleg Stephanwhite (oleg)
 *             Fabien Guiraud (fguiraud)
 *
 *
 * @todo - persistante mofidied data (apc, registry ?)
 *       -
 *
 */

abstract class BaseZF_Item_Db_Abstract extends BaseZF_Item_Abstract
{
    /**
     * Cache Key Template used by BaseZF_Item_Db_Query Class
     */
    const CACHE_KEY_TEMPLATE = '__id__';

    /**
     * Db table associate to this item
     */
    protected $_table = null;

    /**
     * Reference or array with columns types and other informations
     * about current table's columns
     */
    protected $_columns = array();

    /**
     * Realtime mean do not use cache
     */
    protected $_realtime = false;

    //
    // Singleton Item instance manager
    //

    public static function getInstance($table, $id = null, $realtime = false, $className = __CLASS__)
    {
        $item = parent::_getInstance($id, $className);
        $item->_setTable($table);
        $item->setRealTime($realtime);

        return $item;
    }


    /**
     * Get if realtime is enable
     *
     * @return bool true if enable
     */
    final public function isRealTime()
    {
        return $this->_realtime;
    }

    /**
     * Define if Item use cache or not
     *
     * @param bool $realtime set if realtime is enable or not
     *
     * @return object current item instance
     */
    final public function setRealTime($realtime = true)
    {
        $this->_realtime = $realtime;

        return $this;
    }

    //
    // Cache and Db instance getter
    //

    /**
     * Retrieve the Zend_Db database conexion instance
     *
     * @return object Zend_Db_Adapter instance
     */
    abstract protected function _getDbInstance();

    /**
     * Retrieve the Zend_Cache instance
     *
     * @return object Zend_Cache_Core instance
     */
    abstract protected function _getCacheInstance();

    /**
     * Retrieve the Zend_Log logger instance
     *
     * @return object Zend_Log instance
     */
    abstract protected function _getLogInstance();


    /**
     * Retreive the schema, can be an array, Zend_config instance, Zend_Db instance or
     * a BaseZF_Item_Db_Schema_Abstract className
     *
     * @return mixed
     */
    abstract protected function &_getDbChema();

    //
    // Schema functions
    //

    /**
     * Get current table
     *
     * @return string table of dbitem
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Set current table and init columns and properties
     * Can be use to reset Item Db instance
     *
     * @return string table of dbitem
     */
    final protected function _setTable($table)
    {
        // if table not initialized
        if ($this->_table !== $table) {

            // unload current object
            $this->unload();

            $this->_table = $table;

            // extract structure and set has current by reference
            $this->_columns = $this->getTableColumns($this->_table, get_class($this));

            // init properties default values
            $this->_initProperties(array_keys($this->_columns));
        }

        return $this;
    }

    /**
     * Get Primary Key
     *
     * @return string primary key field name
     */
    final public function getColumnPrimaryKey()
    {
        static $primaryKey = null;

        if (is_null($primaryKey)) {

            $columns = $this->getColumns();
            foreach ($columns as $name => $description) {
                if ($description['PRIMARY'] == true) {
                    $primaryKey = $name;
                    break;
                }
            }
        }

        if (mb_strlen($primaryKey) == 0) {
            throw new BaseZF_Item_Db_Exception(sprintf('Unable found Item Db primary key for table "%s"', $this->getTable()));
        }

        return $primaryKey;
    }

    /**
     * Returns the column descriptions for current item table.
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
    final public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * Get names of columns
     *
     * @return array an array of item columns names
     */
    final public function getColumnsNames()
    {
        return array_keys($this->_columns);
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
    final public function getTableColumns($tableName)
    {
        $dbSchema = $this->_getDbChema();

        if (is_string($dbSchema)) {
            $columns = call_user_func(array($dbSchema, 'getTableColumns'), $tableName);
        } else if (is_array($dbSchema)) {
            // @todo
        } else if ($dbSchema instanceOf Zend_Config) {
            // @todo
        } else if ($dbSchema instanceOf Zend_Db) {
            // @todo
        }

        return $columns;
    }

    //
    // CacheKey Id
    //

    /**
     * Build Item cache key
     *
     * @return string a unique item cache key
     */
    protected function _getCacheKey($id = null, $table = null)
    {
        if (is_null($id)) $id = $this->getId();
        if (is_null($table)) $table = $this->getTable();

        return get_class($this) . '_' . $table . '_' . $id ;
    }

    //
    // Select
    //

    /**
     * Build Zend_Db_Select get retreive item columns value
     *
     * @return object ready to use Zend_Db_Select instance
     */
    protected function _getQuery()
    {
        $select = $this->_getDbInstance()->select();

        $select->from($this->getTable())
               ->reset('columns')
               ->columns($this->getColumnsNames())
               ->where($this->getColumnPrimaryKey() . ' IN(:' . $this->getColumnPrimaryKey() . ')');

        $query = $select->assemble();

        // free dbSelect Instance
        unset($select);

        return $query;
    }

    /**
     * Load item columns value from database
     *
     * @param array $ids
     * @param mixed
     * @param int
     *
     * @return array
     */
    protected function _loadData($ids, $realTime = null, $cacheExpire = BaseZF_Item_Db_Query::EXPIRE_NEVER)
    {
        // get ressources instances
        $db     = $this->_getDbInstance();
        $cache  = $this->_getCacheInstance();
        $logger = $this->_getLogInstance();

        // get query params
        $primaryKey = $this->getColumnPrimaryKey();
        $fields     = $this->getColumnsNames();
        $query      = $this->_getQuery();

        $cacheKeyTemplate = $this->_getCacheKey(self::CACHE_KEY_TEMPLATE);

        if ($realTime === null) {
            $realTime = $this->isRealTime();
        }

        // new dbQuery
        $dbQuery = new BaseZF_Item_Db_Query($query, $cacheKeyTemplate, $db, $cache, $logger);
        $dbQuery->setQueryFields($fields);
        $dbQuery->setCacheExpire($cacheExpire);
        $dbQuery->setRealTime($realTime);
        $dbQuery->bindValue($primaryKey, $ids);
        $dbQuery->setCacheKeyByRows($primaryKey, self::CACHE_KEY_TEMPLATE);

        try {

            $dbQuery->execute();
            $data = $dbQuery->fetchAll();

        } catch (BaseZF_Item_Db_Query_Exception_NoResults $e) {

            $data = array();
        }

        // free dbQuery Instance
        unset($dbQuery);

        return $data;
    }

    //
    // Insert
    //


    /**
     * Insert new record to database
     *
     * @param array $propertyies assotiative array of properties
     *
     * @throw BaseZF_Item_Db_Exception
     * @return object instance of BaseZF_Item_Db_Abstract alias current object instance for more fluent interface
     */
    protected function _insert(array $properties)
    {
        if (empty($properties)) {
            throw new BaseZF_Item_Db_Exception(sprintf('Unable insert item to table "%s" cause: empty array of properties provided', $this->getTable()));
        }

        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $primaryKey = $this->getColumnPrimaryKey();

        try {

            // @todo clean property by types

            // add row
            $db->insert($this->getTable(), $properties);

            // get id
            $id = ( empty($properties[$primaryKey]) ) ? $db->lastInsertId($this->getTable(), $primaryKey) : $properties[$primaryKey];

            // clear cache
            $cache->remove($this->_getCacheKey($id));

        } catch (Exception $e) {

            throw new BaseZF_Item_Db_Exception(sprintf('Unable insert item to table "%s" cause: %s' , $this->getTable(), $e->getMessage()));
        }

        return $id;
    }

    //
    // Update
    //

    /**
     * Update modified record to database
     *
     * @param array $propertyies assotiative array of properties
     *
     * @throw BaseZF_Item_Db_Exception
     * @return object instance of BaseZF_Item_Db_Abstract alias current object instance for more fluent interface
     */
    protected function _update($id, $properties)
    {
        if (empty($id)) {
            throw new BaseZF_Item_Db_Exception(sprintf('Unable update item in table "%s" cause: empty id value', $this->getTable()));
        }

        if (empty($properties)) {
            return false;
        }

        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $primaryKey = $this->getColumnPrimaryKey();

        try {

            // @todo clean property by types

            // update row
            $db->update($this->getTable(), $properties, $primaryKey . ' = ' . $db->quote($id));

            // clear cache
            $cache->remove($this->_getCacheKey());

        } catch (Exception $e) {

            throw new BaseZF_Item_Db_Exception(sprintf('Unable update item in table "%s" cause: %s', $this->getTable(), $e->getMessage()));
        }

        return true;
    }

    //
    // Delete
    //

    /**
     * Delete record from database
     *
     * @param integer $id unique key
     *
     * @throw BaseZF_Item_Db_Exception
     * @return object instance of BaseZF_Item_Db_Abstract alias current object instance for more fluent interface
     */
    protected function _delete($ids)
    {
        if (empty($ids)) return false;

        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $primaryKey = $this->getColumnPrimaryKey();
        $ids = (is_array($ids) ? $ids : array($ids));

        try {

            // remove row
            $where = $primaryKey . ' IN (' . implode(', ', array_map(array($db, 'quote'), $ids)) . ')';
            $db->delete($this->getTable(), $where);

            // clear cache
            foreach ($ids as $id) {
                $cache->remove($this->_getCacheKey($id));
            }

        } catch (Exception $e) {
            throw new BaseZF_Item_Db_Exception(sprintf('Unable delete item(s) from table "%s" with ids "%s" cause: %s', $this->getTable(), var_export($ids, true), $e->getMessage()));
        }

        return true;
    }

    /**
     * Check if object exists into database
     *
     * @return bool true if record exist into database
     */
    final public function exists()
    {
        if (empty($this->_id)) {
            return false;
        }

        $property = $this->getColumnPrimaryKey();

        if (!$this->isPropertyLoaded($property)) {
            $this->_loadProperty($property);
        }

        return $this->isPropertyLoaded($property);
    }

    //
    // Serialize
    //

    /**
     * Callback fo serialize oject
     * @note: we serialize only usefull properties, id and if realtime instance
     */
    public function __sleep()
    {
        return array (
            '_id',
            '_realtime',
            '_table',
        );
    }

    /**
     * Callback for unserialize object
     */
    public function __wakeup()
    {
        $this->__construct($this->_table, $this->_id, $this->_realtime);
    }
}

