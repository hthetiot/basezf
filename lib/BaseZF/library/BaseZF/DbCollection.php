<?php
/**
 * DbCollection class in /BaseZF
 *
 * @category   BaseZF
 * @package    BaseZF_DbItem
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

abstract class BaseZF_DbCollection implements Iterator, Countable
{
    /**
     * Define the zend log priority
     */
    const LOG_PRIORITY = 9;

    /**
     * Unique Id
     */
    protected $_ids = array();

    /**
     * Db table associate to this item
     */
    protected $_table;

    /**
     * Reference or array with field types and other information about current table
     */
    protected $_structure;

    /**
     * Reference or array with Schema of database
     */
    protected static $_STATIC_SCHEMA;

    /**
     * Bool for realtime data
     */
    protected $_realtime = false;

    protected $_cacheExpire = BaseZF_DbQuery::EXPIRE_NEVER;

    /**
     * Cache Key Template used by DbQuery Class
     */
    const _CACHE_KEY_TEMPLATE = '__id__';

    /**
     * Saved position f iterator
     */
    protected $_iteratorSavedPosition = array();

    /**
     * Max DbItem extract perQuery
     */
    const MAX_ITEM_BY_REQUEST = 100;

    //
    // Constructor
    //

    /**
     * Constructor
     *
     * @param array $ids array of unique id
     * @param boolean $realtime disable cache
     */
    public function __construct($table = '', array $ids = array(), $realtime = false)
    {
        $this->_table = $table;

        $this->loadStructure($table);
        $this->setRealTime($realtime);
        $this->setIds($ids);

        // init default filter
        $this->filterReset();

        $this->log('Create DbCollection Instance : ' . $this);
    }

    /**
     * Destroy instance of object
     */
    public function __destruct()
    {

    }

    //
    // Cache and Db instance getter
    //

    /**
     * Retrieve the Db instance
     */
    abstract protected function _getDbInstance();

    /**
     * Retrieve the Cache instance
     */
    abstract protected function _getCacheInstance();

    /**
     * Retrieve the Logger instance
     */
    abstract protected function _getLoggerInstance();

    /**
     * Retrieve the Database Schema as array
     */
    abstract protected function &_getDbSchema();

    /**
     * Retrieve the Db Select instance
     */
    protected function _getDbSelectInstance()
    {
        static $select;

        if (!isset($select)) {
            $select = $this->_getDbInstance()->select();
        }

        return $select;
    }

    //
    // Data mapping
    //

    final protected function loadStructure($table)
    {
        $schema = $this->_getDbSchema();

        if (!isset($schema[$table])) {
            throw new BaseZF_DbItem_Exception('There no table "' . $table . '" in schema for BaseZF_DbItem' );
        }

        $this->_structure = &$schema[$table];

        // create string of fields in fomat: <field1> AS <alias1>, <field2> AS <alias2>, ....
        if (!isset($this->_structure['values'])) {
            foreach ($this->_structure['fields'] as $field => $type) {
                $value = $table . '.' . $field;
                $this->_structure['values'][$field] = $value . ' AS ' . $field;
            }
        }

        return $this;
    }

    //
    // Some getter and setter
    //

    /**
     * Setter for DbItem ids of DbCollections
     *
     * @param void $ids unique DbObject id
     *
     * @return $this for more fluent interface
     */
    final public function setIds(array $ids)
    {
        $ids = array_unique($ids);

        foreach ($ids as $k => $id) {
            $ids[$k] = BaseZF_DbItem::getIdFromExtendedId($id);
        }

        $this->_ids = $ids;
        foreach ($this as $item); // create DbItem object for each id if not exist
        $this->rewind();
        return $this;
    }

    /**
     * Getter for DbItem ids of DbCollections
     *
     * @return void $this->_ids value
     */
    final public function getIds()
    {
        return $this->_ids;
    }

    /**
     * Getter for DbItem ids of DbCollections
     *
     * @return void $this->_ids value
     */
    final public function addId($id)
    {
        if (!is_null($id) && !in_array($id, $this->_ids)) {
            $this->_ids[] = $id;
            $this->getItem($id);
        }
        if (!$this->valid()) $this->rewind();
        return $this;
    }

    /**
     * Getter for DbItem ids of DbCollections
     *
     * @return void $this->_ids value
     */
    final public function removeId($id)
    {
        if (($key = array_search($id, $this->_ids)) !== FALSE) {

            $item = $this->getItem($id);
            $item->removeCollection($this);

            unset($this->_ids[$key]);
        }
        return $this;
    }

    /**
     * Getter for random id of DbItem
     *
     * @return integer $this->_ids value
     */
    public function getRandomId($count=1)
    {
        if (empty($this->_ids)) return false;
        $this->_saveIteratorPosition();
        try {
            $idx = array_rand($this->_ids, $count);
            if ($count>1 && is_array($idx)) {
                $result = array();
                foreach ($idx as $i) {
                    $result[] = $this->_ids[$i];
                }
            } else {
                $result = $this->_ids[$idx];
            }
        } catch (Exception $e) {
            $this->_loadIteratorPosition();
            throw $e;
        }
        $this->_loadIteratorPosition();
        return $result;
    }

    /**
     * Define if object use cache or not
     *
     * @param bool $realtime set if realtime is enable or not
     *
     * @return $this for more fluent interface
     */
    final public function setRealTime($realtime = true)
    {
        $this->_realtime = $realtime;

        return $this;
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
     * Get table name
     *
     * @return string table name
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Get Primary Key
     *
     * @return string primary key field name
     */
    public function getPrimaryKey()
    {
        return $this->_structure['primary'];
    }

    //
    // Filters
    //

    public function filterReset()
    {
        // set default query
        $select = $this->_getDbSelectInstance();

        $select->reset();

        // build default query
        $select->from($this->getTable())
               ->reset(Zend_Db_Select::COLUMNS)
               ->columns($this->getPrimaryKey());

        return $this;
    }

    /**
     * Set the table name or expression with INNER JOIN parts and so on
     *
     * @param string $table - table name or expression
     */
    public function filterTable($table)
    {
        $this->_getDbSelectInstance()->from($table);

        return $this;
    }

    /**
     * Set filter WHERE expression
     *
     * @param string $where
     */
    public function filterWhere($where, $value = null)
    {
        $this->_getDbSelectInstance()->where($where, $value);

        return $this;
    }

    /**
     * Set filter ORDER BY column list
     *
     * @param string $orderBy
     *
     * @return $this for more fluent interface
     */
    public function filterOrderBy($orderBy)
    {
        $this->_getDbSelectInstance()->order($orderBy);

        return $this;
    }

    /**
     * Set filter GROUP BY column list
     *
     * @param string $groupBy
     *
     * @return BaseZf_DbCollection - $this for more fluent interface
     */
    public function filterGroupBy($groupBy)
    {
        $this->_getDbSelectInstance()->group($groupBy);

        return $this;
    }

    /**
     * Set filter LIMIT and OFFSET range
     *
     * @param integer $limit
     * @param integer $offset
     *
     * @return BaseZf_DbCollection - $this for more fluent interface
     */
    public function filterLimit($limit, $offset = null)
    {
        $this->_getDbSelectInstance()->limit($limit, $offset);

        return $this;
    }

    /**
     * Set expire period for cache
     *
     * @return string SQL query
     */
    public function filterCache($expire = BaseZF_DbQuery::EXPIRE_NEVER)
    {
        $this->_cacheExpire = $expire;

        return $this;
    }

    /**
     * Execute filter
     *
     * @return $this for more fluent interface
     */
    public function filterExecute($cacheKey = null)
    {
        // get query
        $select = $this->_getDbSelectInstance();

        if (is_null($cacheKey)) {
            $cacheKey = self::_buildQueryCacheKey($select);

            $this->_updatePerPageCache($select);
        }

        try {

            $fields = array($this->getPrimaryKey());
            $dbQuery = $this->_getDbQuery($select->assemble(), $cacheKey, $fields);

            // exec query throw dbQuery
            $dbQuery->execute();

            // add ids to collection
            $ids = array();
            while ($row = $dbQuery->fetch()) {
                $ids[] = $row[$this->getPrimaryKey()];
            }

            $this->setIds($ids);

        // if no results found
        } catch (BaseZF_DbQuery_Exception_NoResults $e) {
            $this->setIds(array());
        }

        // free dbQuery Instance
        unset($dbQuery);

        return $this;
    }

    protected function _getDbSelectCountInstance()
    {
        $selectCount = clone($this->_getDbSelectInstance());

        $selectCount->reset(Zend_Db_Select::COLUMNS)
                    ->reset(Zend_Db_Select::LIMIT_COUNT)
                    ->reset(Zend_Db_Select::LIMIT_OFFSET)
                    ->reset(Zend_Db_Select::ORDER)
                    ->columns('count(*) as nb');

        return $selectCount;
    }

    /**
     * Get rocords count for filter
     *
     * @return int nb results for current filters
     */
    public function filterCount($cacheKey = null)
    {
        // clone main query and build count one
        $selectCount = $this->_getDbSelectCountInstance();

        if (is_null($cacheKey)) {
            $cacheKey = self::_buildQueryCacheKey($selectCount);
        }

        try {

            $fields = array('nb');
            $dbQuery = $this->_getDbQuery($selectCount->assemble(), $cacheKey, $fields);

            // exec query throw dbQuery
            $dbQuery->execute();
            $data = $dbQuery->fetch();

            $results = (isset($data['nb']) ? $data['nb'] : 0);

        } catch (BaseZF_DbQuery_Exception_NoResults $e) {

            $results = 0;
        }

        // free dbQuery Instance
        unset($dbQuery);

        return $results;
    }

    final protected static function _buildQueryCacheKey(Zend_Db_Select $select)
    {
        $cacheKey = sha1(serialize(array(
            $select->getPart(Zend_Db_Select::COLUMNS),
            $select->getPart(Zend_Db_Select::FROM),
            $select->getPart(Zend_Db_Select::WHERE),
            $select->getPart(Zend_Db_Select::GROUP),
            $select->getPart(Zend_Db_Select::HAVING),
            $select->getPart(Zend_Db_Select::ORDER),
            $select->getPart(Zend_Db_Select::LIMIT_COUNT),
            $select->getPart(Zend_Db_Select::LIMIT_OFFSET),
        )));

        return $cacheKey;
    }

    final public function _getDbQuery($query, $cacheKey = null, array $fields = array())
    {
        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $logger = $this->_getLoggerInstance();

        // new dbQuery
        $dbQuery = new BaseZF_DbQuery($query, $cacheKey, $db, $cache, $logger);
        $dbQuery->setQueryFields($fields);
        $dbQuery->setCacheExpire($this->_cacheExpire);
        $dbQuery->setRealTime($this->isRealTime());

        return $dbQuery;
    }

    /**
     * Clear filterCache
     *
     * @return string SQL query
     */
    final public function clearCache($cacheKey = null)
    {
        $cache = $this->_getCacheInstance();

        if (is_null($cacheKey)) {

            // flush cache for filter
            $select = $this->_getDbSelectInstance();
            $cacheKey = $this->_buildQueryCacheKey($select);
            $cache->remove($cacheKey);

            // flush cache for filterCount
            $selectCount = $this->_getDbSelectCountInstance();
            $cacheKeyCount = $this->_buildQueryCacheKey($selectCount);
            $cache->remove($cacheKeyCount);

        } else {

            // flush cache from param
            $cache->remove($cacheKey);
        }

        return $this;
    }

    /**
     * Clear cache for query which uses paging
     *
     * @param interger $perPage
     * @param integer $recordCount
     */
    public function clearPerPageCache($perPage = null, $recordCount = null)
    {
        return;

        $select = $this->_getDbSelectInstance();
        $cache = $this->_getCacheInstance();

        // build main cachekey
        $cacheKey = self::_buildPerPageCacheKey($select);

        if ($cacheKeys = $cache->load($cacheKey)) {

            // clear main cachekey
            $cache->remove($cacheKey);

            // clear sub cachekeys
            foreach ($cacheKeys as $cacheKey) {
                $cache->remove($cacheKey);
            }
        }

        return $this;
    }

    public function _updatePerPageCache(Zend_Db_Select $select)
    {
        return;

        $select = $this->_getDbSelectInstance();
        $cache = $this->_getCacheInstance();

        // build main cachekey
        $cacheKey = self::_buildPerPageCacheKey($select);

        // get data from cache
        if (!$cacheKeys = $cache->load($cacheKey)) {
            $cacheKeys = array();
        }

        $currentCacheKey = $this->_buildQueryCacheKey($select);

        if (in_array($currentCacheKey, $cacheKeys) === false) {

            // add data entry
            $cacheKeys[] = $currentCacheKey;

            // save to cache
            $cache->save($cacheKeys, $cacheKey);
        }

        return $this;
    }

    final protected static function _buildPerPageCacheKey(Zend_Db_Select $select)
    {
        $cacheKey = sha1(serialize(array(
            $select->getPart(Zend_Db_Select::COLUMNS),
            $select->getPart(Zend_Db_Select::FROM),
            $select->getPart(Zend_Db_Select::WHERE),
            $select->getPart(Zend_Db_Select::GROUP),
            $select->getPart(Zend_Db_Select::HAVING),
            $select->getPart(Zend_Db_Select::ORDER),
        )));

        return $cacheKey;
    }

    //
    // Filter Delete
    //

    /**
     * Delete records using filter params
     *
     * @return this
     */
    public function filterDelete()
    {
        // exec filter
        $this->filterExecute();

        // delete item
        foreach ($this as $id => $item) {
            $item->delete();
        }

        // clear cache
        $this->clearCache();

        // clear ids
        $this->setIds(array());

        return $this;
    }

    //
    // Paginator manager
    //

    //
    // DbItem manager
    //

    protected function _getDbItemClassName($collClassName = null)
    {
        if (is_null($collClassName)) {
            $collClassName = get_class($this);
        }

        $itemClassName = str_replace('_DbCollection', '_DbItem', $collClassName);

        try {

            Zend_Loader::loadClass($itemClassName);

        } catch (BaseZF_Error_Exception  $e) {

            $itemClassName = $this->_getItemClassName('BaseZF_DbCollection');
        }

        return $itemClassName;
    }

    /**
     * Retreive item object by id if exist
     *
     * @return BaseZF_DbItem object instance
     */
    public function getItem($id)
    {
        if (!in_array($id, $this->_ids)) {
            throw new BaseZF_DbCollection_Exception('item with id "' . $id . '" not found in this collection');
        }

        $itemClassName = $this->_getDbItemClassName();
        $item = call_user_func(array($itemClassName, 'getInstance'), $this->_table, $id, $this->isRealTime());

        // add collection dependency
        $item->addCollection($this);

        return $item;
    }

    /**
     * Create a new item and associate it to collection
     *
     * @return DbItem object instance of new item
     */
    public function newItem($data)
    {
        // filter data
        $data = array_map('trim', $data);

        $itemClassName = $this->_getDbItemClassName();
        $newItem = call_user_func(array($itemClassName, 'getInstance'), $this->_table, null, $this->isRealTime());

        $newItem->setProperties($data);
        $newItem->insert();

        // add item to collection
        $this->addId($newItem->getId());

        return $newItem;
    }

    //
    // DbItem data
    //

    /**
     * Retrieve property items values has array
     *
     * @param string property item name
     *
     * @return array of property value
     */
    public function getProperty($property)
    {
        $result = array();
        $this->_saveIteratorPosition();
        try {
            foreach ($this as $id => $item) {
                $result[$id] = $item->$property;
            }
        } catch (Exception $e) {
            $this->_loadIteratorPosition();
            throw $e;
        }
        $this->_loadIteratorPosition();
        return $result;
    }

    /**
     * Modify a propery: change value and mark as modified for all items
     *
     * @param string $property Property to modify
     * @param string $value New value
     * @param boolean $check Check identical values before update (default true)
     *
     * @return boolean modified state
     */
    public function setProperty($property, $value)
    {
        $this->_saveIteratorPosition();
        try {
            foreach ($this as $id => $item) {
                $item->setProperty($property,$value);
            }
        } catch (Exception $e) {
            $this->_loadIteratorPosition();
            throw $e;
        }
        $this->_loadIteratorPosition();

        return $this;
    }

    /**
     * Modify properties: change values for multiple properties and mark as modified for all items
     *
     * @param array assotiative array of values
     *
     * @return boolean modified state
     */
    public function setProperties($data)
    {
        $this->_saveIteratorPosition();
        try {
            foreach ($this as $id => $item) {
                $item->setProperties($data);
            }
        } catch (Exception $e) {
            $this->_loadIteratorPosition();
            throw $e;
        }
        $this->_loadIteratorPosition();
        return $this;
    }

    public function update()
    {
        $items = array();

        $this->_saveIteratorPosition();
        try {
            foreach ($this as $id => $item) {
                if ($item->isModified()) $items[] = $item;
            }
        } catch (Exception $e) {
            $this->_loadIteratorPosition();
            throw $e;
        }
        $this->_loadIteratorPosition();

        if (!empty($items)) {
            BaseZF_DbItem::massUpdate($items);
        }
        return $this;
    }

    public function delete()
    {
        $items = array();

        $this->_saveIteratorPosition();

        try {
            foreach ($this as $id => $item) {
                $items[$id] = $item;
            }
        } catch (Exception $e) {
            $this->_loadIteratorPosition();
            throw $e;
        }

        $this->_loadIteratorPosition();

        if (!empty($items)) {
            BaseZF_DbItem::massDelete($items);
        }

        $this->setIds(array());

        return $this;
    }

    //
    // Implement Countable
    //

    public function count()
    {
        return count($this->_ids);
    }

    //
    // Implement Iterator
    //

    public function rewind()
    {
        reset($this->_ids);
        return $this;
    }

    public function current()
    {
        $var = current($this->_ids);

        return ($var === false)? false : $this->getItem($var);
    }

    public function key()
    {
        $var = current($this->_ids);
        return $var;
    }

    public function next()
    {
        $var = next($this->_ids);
        return ($var === false)? false : $this->getItem($var);
    }

    public function valid()
    {
        $var = current($this->_ids) !== false;
        return $var;
    }

    //
    // Iterator position management
    //

    /**
     * Save iterator position of $this->_dbobjects array
     *
     * @return $this for more fluent interface
     */
    protected function _saveIteratorPosition()
    {
        if (count($this->_iteratorSavedPosition) > 40) {
            throw new BaseZF_DbCollection_Exception('maximal depth reached');
        }

        $key = key($this->_ids);
        if ($key === NULL) $key = 0;
        array_push($this->_iteratorSavedPosition, $key);

        return $this;
    }

    /**
     * Load saved iterator position of $this->_items array
     *
     * @return $this for more fluent interface
     */
    protected function _loadIteratorPosition()
    {
        $pos = array_pop($this->_iteratorSavedPosition);

        if ($pos === NULL) {
            throw new BaseZF_DbCollection_Exception('No saved position for iterator.');
        }

        array_set_current($this->_ids, $pos);
        return $this;
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
        return array
        (
            '_ids',
            '_realtime',
        );
    }

    /**
     * Callback for unserialize object
     */
    public function __wakeup()
    {
        $this->__construct($this->_table, $this->_ids, $this->_realtime);
    }

    //
    // Tools
    //

    /**
     * string builder called to display object has string
     */
    public function __toString()
    {
        return 'table: "' . $this->_table . '" :: ' . get_class($this) . '::' . implode(':', $this->getIds());
    }

    //
    // Logger tools
    //

    /**
     * Send log message to BaseZF_Framework_Log instance
     *
     * @param string $msg log message
     */
    public function log($msg)
    {
        if ($logger = $this->_getLoggerInstance()) {
            $logger->log('DbCollection -> ' . $msg, self::LOG_PRIORITY);
        }
    }
}

