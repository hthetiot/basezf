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
    protected $_table;

    /**
     * Reference or array with field types and other information about current table
     */
    protected $_structure;

    /**
     * Realtime do not use cache
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
     * Get is realtime is enable
     *
     * @return bool true if enable
     */
    final public function isRealTime()
    {
        return $this->_realtime;
    }

    /**
     * Define if object use cache or not
     *
     * @param bool $realtime set if realtime is enable or not
     *
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    final public function setRealTime($realtime = true)
    {
        $this->_realtime = $realtime;
        if ($realtime) {
            $this->_data = array();
        }
        return $this;
    }

    //
    // Cache and Db instance getter
    //

    /**
     * Retrieve the Zend_Db database conexion instance
     */
    abstract protected function _getDbInstance();

    /**
     * Retrieve the Zend_Cache instance
     */
    abstract protected function _getCacheInstance();

    /**
     * Retrieve the Zend_Log logger instance
     */
    abstract protected function _getLogInstance();

    /**
     * Retrieve the Database Schema as array
     */
    abstract protected function _getTableStructure();

    //
    // Schema functions
    //

    /**
     * Get current table
     *
     * @return string table of dbitem
     */
    final public function getTable()
    {
        return $this->_table;
    }

    /**
     * Set current table
     *
     * @return string table of dbitem
     */
    final protected function _setTable($table)
    {
        $this->_table = $table;

        $this->_structure = $this->_getTableStructure($table);

        return $this;
    }

    /**
     * Get Primary Key
     *
     * @return string primary key field name
     */
    final protected function _getPrimaryKeyColumn()
    {
        static $primaryKey = null;

        if (is_null($primaryKey)) {

            foreach ($this->_structure as $columnName => $columnData) {
                if ($columnData['PRIMARY'] == true) {
                    $primaryKey = $columnName;
                    break;
                }
            }

            if (empty($primaryKey)) {
                throw new BaseZF_Item_Db_Exception(sprintf('Unable found Item db primary key for table "%s"', $this->getTable()));
            }
        }

        return $primaryKey;
    }

    /**
     * Get type of field.
     *
     * @return array an array of item columns names
     */
    final protected function _getColumnsNames()
    {
        return array_keys($this->_structure);
    }

    /**
     * Get type of field.
     *
     * @param string $columnName field name
     * @return @todo
     */
    final protected function _getColumnType($columnName)
    {
        return isset($this->_structure[$columnName]) ? $this->_structure[$columnName] : false;
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

        return 'dbItem_' . $table . '_' . $id ;
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
               ->columns($this->_getColumnsNames())
               ->where($this->_getPrimaryKeyColumn() . ' IN(:' . $this->_getPrimaryKeyColumn() . ')');

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
        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $logger = $this->_getLogInstance();

        $primaryKey = $this->_getPrimaryKeyColumn();
        $fields = $this->_getColumnsNames();
        $query = $this->_getQuery();

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
        $dbQuery->setCacheKeyByRows( $primaryKey, self::CACHE_KEY_TEMPLATE);

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
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    protected function _insert(array $properties)
    {
        if (empty($properties)) {
            throw new BaseZF_Item_Db_Exception(sprintf('Unable insert item to table "%s" cause: empty array of properties provided', $this->getTable()));
        }

        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $primaryKey = $this->_getPrimaryKeyColumn();

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
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
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
        $primaryKey = $this->_getPrimaryKeyColumn();

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
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    protected function _delete($ids)
    {
        if (empty($ids)) return false;

        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $primaryKey = $this->_getPrimaryKeyColumn();
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

        $property = $this->_getPrimaryKeyColumn();

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

