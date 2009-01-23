<?php
/**
 * DbCollection class in /BaseZF
 *
 * @category   BaseZF_DbCollection
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 Bahu
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_DbCollection implements Iterator, Countable
{
    /**
     * Table name
     */
    protected $_table;

    /**
     * Reference or array with field types and other information about current table
     */
    protected $_structure;
    
    /**
     * Reference or array Schema of database
     */
    protected static $_SCHEMA;

    /**
     * Unique Ids
     */
    protected $_ids = array();

    /**
     * Bool for realtime data
     */
    protected $_realtime = false;

    /**
     * Saved position f iterator
     */
    protected $_iteratorSavedPosition = array();

    /**
     * generated query
     */
    protected $_query = null;

    /**
     * Array for Filter property
     */
    protected $_propertyFilter = array();

    /**
     * Filter parmas
     */
    protected $_params = array();

    /**
     * Cache expire period.
     * No cache by default
     */
    protected $_cacheExpire = BaseZF_DbQuery::EXPIRE_NONE;

    const MAX_ITEM_BY_REQUEST = 100;

    protected $_queryCacheInstances = array();

    /**
     * Constructor
     *
     * @param array $ids array of unique id
     * @param boolean $realtime disable cache
     */
    public function __construct($table = '', array $ids = array(), $realtime = false)
    {
        $this->_table = $table;
        $this->_structure = &$this->loadStructure($table);
        $this->setRealTime($realtime);
        $this->setIds($ids);

        $this->log('Create DbCollection Instance : ' . $this);
    }

    /**
     * Destroy instance of object
     */
    public function __destruct()
    {
        $ids = $this->_ids;
        foreach ($ids as $id) {
            if($item = BaseZF_DbItem::getExistInstance($this->_table, $id)){
                $item->removeCollection($this);
            }
        }

        foreach ($this->_queryCacheInstances as $queryCacheInstance) {
            unset($queryCacheInstance);
        }
    }

    protected function getStructure()
    {
        return $this->_structure;
    }

    protected function &loadStructure()
    {
        $schema = $this->_getDbSchema();
        
        if (!isset($schema[$this->_table])) {
            throw new BaseZF_DbCollection_Exception('There no table "' . $table . '" in schema for BaseZF_DbItem' ); 
        }
        
        return $schema[$this->_table];
    }

    //
    // Database mapping
    //

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
    // Ids of Collection
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
        if(!is_null($id) && !in_array($id, $this->_ids)) {
            $this->_ids[] = $id;
            $this->getItem($id);
        }
        if(!$this->valid()) $this->rewind();
        return $this;
    }

    /**
     * Getter for DbItem ids of DbCollections
     *
     * @return void $this->_ids value
     */
    final public function removeId($id)
    {
        if(($key = array_search($id, $this->_ids)) !== FALSE) {
            unset($this->_ids[$key]);
        }
        return $this;
    }

    //
    // Some getter and setter
    //

    /**
     * Getter for random id of DbItem
     *
     * @return integer $this->_ids value
     */
    public function getRandomId($count=1)
    {
        if(empty($this->_ids)) return false;
        $this->_saveIteratorPosition();
        try {
            $idx = array_rand($this->_ids, $count);
            if($count>1 && is_array($idx)) {
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
    final public function setRealTime($realtime = true) {
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
    abstract protected function _getDbSchema();

    //
    // Db tools
    //

    /**
     * Prepare, cache and return a SQL statement
     *
     * @param string $query SQL query
     *
     * @return object a new statement of a old if allready called
     */
    final protected function _getStatement($query, $db = null)
    {
        if (is_null($db)) {
            $db = $this->_getDbInstance();
        }

        return $db->getStatement($query);
    }

    /**
     * Get a queryCache instance for a new SQL query
     *
     * @param string $query SQL query
     * @param string $cacheKey use a specific cache key
     * @param object $db use a specific instance of db
     * @param object $cache use a specific instance of cache
     *
     * @return object instance of BaseZF_DbQuery
     */
    final protected function _getDbQuery($query, $cacheKey = null, $fieldsList=array(), $realTime=false, $expire = BaseZF_DbQuery::EXPIRE_DAY ,$db = null, $cache = null, $logger = null)
    {
        if (is_null($db)) {
            $db = $this->_getDbInstance();
        }

        if (is_null($cache)) {
            $cache = $this->_getCacheInstance();
        }

        if (is_null($logger)) {
            $logger = $this->_getLoggerInstance();
        }

        // new queryCache
        $queryCache = new BaseZF_DbQuery($query, $cacheKey, $db, $cache, $logger);
        $queryCache->setQueryFields($fieldsList);
        $queryCache->setCacheExpire($expire);

        // configure queryCache reltime
        $queryCache->setRealTime($realTime);

        $this->_queryCacheInstances[] = &$queryCache;

        return $queryCache;
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
            $logger->log($msg, BaseZF_Framework_Log::DBOBJECT_PROFILER);
        }
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

    /**
     * Save iterator position of $this->_dbobjects array
     *
     * @return $this for more fluent interface
     */
    protected function _saveIteratorPosition()
    {
        if(count($this->_iteratorSavedPosition)>40) {
            throw new BaseZF_DbCollection_Exception('maximal depth reached');
        }

        $key = key($this->_ids);
        if($key === NULL) $key = 0;
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

        if($pos === NULL) {
            throw new BaseZF_DbCollection_Exception('No saved position for iterator.');
        }

        array_set_current($this->_ids, $pos);
        return $this;
    }

    //
    // Items data management
    //

    //
    // Database data manipulations
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
            foreach ($this as $id=>$item) {
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
                if($item->isModified()) $items[] = $item;
            }
        } catch (Exception $e) {
            $this->_loadIteratorPosition();
            throw $e;
        }
        $this->_loadIteratorPosition();

        if(!empty($items)) {
            Bahu_DbItem::massUpdate($items);
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

        if(!empty($items)) {
            Bahu_DbItem::massDelete($items);
        }

        return $this;
    }

    //
    // Items management
    //

    /**
     * Retreive item object by id if exist
     *
     * @return BaseZF_DbItem object instance
     */
    public function getItem($id)
    {
        if(!in_array($id,$this->_ids)) {
            throw new BaseZF_DbCollection_Exception('item with id "' . $id . '" not found in this collection');
        }

        $item = Bahu_DbItem::getInstance($this->_table, $id, $this->isRealTime());

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
        $newItem = Bahu_DbItem::getInstance($this->_table, null, $this->isRealTime());
        $newItem->setProperties($data);
        $newItem->insert();

        // add item to collection
        $this->addId($newItem->getId());

        return $newItem;
    }

    //
    // Tools
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

    /**
     * string builder called to display object has string
     */
    public function __toString()
    {
        return 'table: "' . $this->_table . '" :: ' . get_class($this) . '::' . implode(':', $this->getIds());
    }

    //
    // Filters
    //

    /**
     * Set the primary key of collection
     *
     * @param string $key - primary key
     * @param string $alias - alias of primary key
     */
    public function filterPrimaryKey($key, $alias = null)
    {
        $this->_query = null;
        $this->_propertyFilter['key'] = $key;
        $this->_propertyFilter['alias'] = ($alias === null) ? $key : $alias;

        return $this;
    }

    /**
     * insert params in query part
     *
     * @param string querypart with inserted paramenters
     */
    protected function setParams($where, $params)
    {
        $q_params = explode( '?', $where );

        if (count($q_params) > 1) {

            $db = $this->_getDbInstance();

            foreach ($q_params as $key => &$p) {
                if (!empty($p)) {
                    if (isset($params[$key])) {
                        $p = $db->quote($p . ' ?', $params[$key] /*, is_numeric($params[$key]) ? 'INTEGER' : ''*/);
                    }
                }
            }

            $where = implode(' ', $q_params);
        }
        return $where;
    }

    /**
     * Set the table name or expression with INNER JOIN parts and so on
     *
     * @param string $table - table name or expression
     */
    public function filterTable($table, $params = array() )
    {
        $this->_query = null;
        $this->_params['table'] = $params;
        $table = $this->setParams($table, $params);

        $this->_propertyFilter['table'] = $table;

        return $this;
    }

    /**
     * Set filter WHERE expression
     *
     * @param string $where
     */
    public function filterWhere($where, $params = array())
    {
        $this->_query = null;
        $this->_params['where'] = $params;
        $where = $this->setParams($where, $params);

        $this->_propertyFilter['where'] = $where;

        return $this;
    }

    /**
     * Set filter ORDER BY column list
     *
     * @param string $orderBy
     *
     * @return $this for more fluent interface
     */
    public function filterOrderBy($orderBy, $params = array())
    {
        $this->_query = null;
        $this->_params['orderBy'] = $params;
        $orderBy = $this->setParams($orderBy, $params);
        $this->_propertyFilter['orderBy'] = $orderBy;

        return $this;
    }

    /**
     * Set filter GROUP BY column list
     *
     * @param string $groupBy
     *
     * @return BaseZf_DbCollection - $this for more fluent interface
     */
    public function filterGroupBy($groupBy, $params = array())
    {
        $this->_query = null;
        $this->_params['groupBy'] = $params;
        $groupBy = $this->setParams($groupBy, $params);
        $this->_propertyFilter['groupBy'] = $groupBy;

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
    public function filterLimit($limit, $offset = 0)
    {
        $this->_query = null;
        $limit = intval($limit);
        $this->_propertyFilter['limit'] = $limit;

        if ($offset > 0 ) {
            $offset = intval($offset);
            $this->_propertyFilter['offset'] = $offset;
        } else {
            unset($this->_propertyFilter['offset']);
        }

        return $this;
    }

    /**
     * Get array of fields
     *
     * @return array field names
     */
    private function getFilterFields()
    {
        return array($this->_propertyFilter['alias']);
    }

    /**
     * Get query string for filters
     *
     * @return string SQL query
     */
    public function getFilterCacheKey($query = null)
    {
        // @todo: check this function
        $cacheKey = '';
        if(empty($query)) {

            $query = $this->_getFilterQuery();
            $cacheKey = $this->_propertyFilter['alias'];
            $cacheKeyParams = $this->_getFilterParamsCacheKey();

            // add param to cacheKey
            if($cacheKeyParams) {
                $cacheKey .= ' ' . $cacheKeyParams;
            }
        }

        // to avoid situation like 3 and '3' which gives different key
        $query = str_replace("'", '', $query);

        $cacheKey .= ' md5{' . md5($query) . '}';

        return $cacheKey;
    }

    protected function _getFilterParamsCacheKey()
    {
        $cacheKeyParams = '';

        foreach ($this->_params as $key => $blockParams) {

            if(count($blockParams) > 0) {
                $blockParams = array_map('strval', $blockParams);
                $cacheKeyParams .= $key{0} . '(' . implode(',', $blockParams) . ')';
            }
        }

       return $cacheKeyParams = 'p{' . $cacheKeyParams . '}';
    }

    protected function _getFilterLimitCacheKey()
    {
        $cacheKey = isset($this->_propertyFilter['alias']) ? $this->_propertyFilter['alias'] : null;
        $cacheKeyParams = $this->_getFilterParamsCacheKey();

        // add param to cacheKey
        if($cacheKeyParams) {
            $cacheKey .= ' ' . $cacheKeyParams;
        }

        return $cacheKey;
    }

    protected function _updateFilterLimitValues()
    {
        $cacheKeyForPagination = $this->_getFilterLimitCacheKey();
        $limit = isset($this->_propertyFilter['limit']) ? $this->_propertyFilter['limit'] : null;
        $offset = isset($this->_propertyFilter['offset']) ? $this->_propertyFilter['offset'] : null;

        // no store if no limit
        if ($offset === null && $limit === null) {
            return $this;
        }

        $cache = $this->_getCacheInstance();

        try {

            $offsetPerLimit = $cache->getWithException($cacheKeyForPagination);

            if (!isset($offsetPerLimit[$limit])) {
                $offsetPerLimit[$limit] = array();
            }

            if(in_array($offset, $offsetPerLimit[$limit]) === false) {

                $offsetPerLimit[$limit][] = $offset;
                $cache->set($cacheKeyForPagination, $offsetPerLimit);
            }

        } catch (BaseZF_Cache_Exception $e) {

            $offsetPerLimit = array(
                $limit => array($offset),
            );

            $cache->set($cacheKeyForPagination, $offsetPerLimit);
        }

        return $this;
    }

    protected function _getFilterLimitValues()
    {
        $this->_updateFilterLimitValues();

        try {

            $cacheKeyForPagination = $this->_getFilterLimitCacheKey();

            $cache = $this->_getCacheInstance();

            $offsetPerLimit = $cache->getWithException($cacheKeyForPagination);

            return $offsetPerLimit;

        } catch (BaseZF_Cache_Exception $e) {

            return array();
        }
    }

    /**
     * Set expire period for cache
     *
     * @return string SQL query
     */
    public function filterCache($expire = BaseZF_DbQuery::EXPIRE_DAY)
    {
        $this->_cacheExpire = $expire;

        return $this;
    }

    /**
     * Clear filterCache
     *
     * @return string SQL query
     */
    public function clearCache($cacheKey = null)
    {
        $query = $this->_getFilterQuery();

        $flushAuto = false;
        if(is_null($cacheKey)) {
            $cacheKey = $this->getFilterCacheKey();
            $flushAuto = true;
        }

        // clear query
        $queryCache = $this->_getDbQuery($query, $cacheKey, $this->getFilterFields(), $this->isRealTime(), $this->_cacheExpire);
        $queryCache->clear();

        // clear ORM dependancy
        if($flushAuto) {

            // clear query count
            $data = $this->_getFilterCountQueryAndFields();
            $query = $data['query'];
            $fields = $data['fields'];

            $cacheKey = $this->getFilterCacheKey($query);
            $queryCache = $this->_getDbQuery($query, $cacheKey, array_merge(array('cnt'),$fields), $this->isRealTime(), $this->_cacheExpire);
            $queryCache->clear();
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
        // auto flushing
        if ($perPage === null) {


            $offsetPerLimit = $this->_getFilterLimitValues();

            foreach ($offsetPerLimit as $limit => $offsets) {
                foreach ($offsets as $offset) {
                    $this->filterLimit($limit, $offset);
                    $this->clearCache();
                }
            }

        // manual flushing
        } else {

            if ( $recordCount === null ) {
                $recordCount = $this->filterCount();
            }

            if ( !is_array($perPage) ) {
                $perPage = array($perPage);
            }

            foreach ($perPage as $perPageValue) {
                $i = 0;
                while (($i*$perPageValue - $recordCount) < $perPageValue) {
                    $this->filterLimit($perPageValue, $i*$perPageValue);
                    $this->clearCache();
                    $i++;
                }
            }
        }

        return $this;
    }

    /**
     * Execute filter
     *
     * @return $this for more fluent interface
     */
    public function filterExecute($cacheKey = null)
    {
        try {

            $query = $this->_getFilterQuery();
            $this->log('filterExecute query : ' . $query);

            if(is_null($cacheKey)) {

                $cacheKey = $this->getFilterCacheKey();

                $this->_updateFilterLimitValues();
            }

            $data = $this->QueryExecute($query, $cacheKey, $this->getFilterFields(), $this->isRealTime(), $this->_cacheExpire);

            $ids = array();
            foreach ($data as $row) {
                $ids[] = $row[$this->_propertyFilter['alias']];
            }

            $this->setIds($ids);

        // if no results found
        } catch (BaseZF_DbQuery_Exception $e) {
            $this->setIds(array());
        }

        return $this;
    }

    public function QueryExecute($query, $cacheKey, $filterFields, $realTime = true, $cacheExpire = BaseZF_DbQuery::EXPIRE_NONE)
    {
        $queryCache = $this->_getDbQuery($query, $cacheKey, $filterFields, $realTime, $cacheExpire);

        try {

            $queryCache->execute();
            $data = $queryCache->fetchAll();

        } catch (BaseZF_DbQuery_Exception_NoResults $e) {
            $data = array();
        }

        if(!is_array($data)) $data = array();
        return $data;
    }

    /**
     * Get query string for filters
     *
     * @return string SQL query
     */
    private function _getFilterQuery()
    {
        if($this->_query) {
            return $this->_query;
        }

        $table = $this->getTable();

        if (!isset($this->_propertyFilter['key'])) {
            if (!$this->getPrimaryKey() || empty($table)) {
                throw new BaseZF_DbCollection_Exception('primary key must be specified present in filter statement');
            }

            $this->filterPrimaryKey($table.'.'.$this->getPrimaryKey(), $this->getPrimaryKey());
        }

        if (!isset($this->_propertyFilter['table'])) {

            if (empty($table)) {
               throw new BaseZF_DbCollection_Exception('property "table" must present in filter statement');
            } else {
                $this->filterTable($table);
            }
        }

        $query = 'SELECT ' . $this->_propertyFilter['key'] . ' AS ' . $this->_propertyFilter['alias'] .
                 ' FROM ' . $this->_propertyFilter['table'];

        if (!empty($this->_propertyFilter['where'])) {
            $query .= ' WHERE ' . $this->_propertyFilter['where'];
        }

        if (!empty($this->_propertyFilter['groupBy'])) {
            $query .= ' GROUP BY ' . $this->_propertyFilter['groupBy'];
        }

        if (!empty($this->_propertyFilter['orderBy'])) {
            $query .= ' ORDER BY ' . $this->_propertyFilter['orderBy'];
        }

        if (!empty($this->_propertyFilter['limit'])) {
            $query .= ' LIMIT ' . $this->_propertyFilter['limit'];
        }
        if (!empty($this->_propertyFilter['offset'])) {
            $query .= ' OFFSET ' . $this->_propertyFilter['offset'];
        }

        $this->_query = $query;

        // store pagination options

        return $this->_query;
    }

    private function _getFilterCountQueryAndFields()
    {
        $this->_getFilterQuery();

        $fields = array();
        if (!empty($this->_propertyFilter['groupBy'])) {
            $fields = array_map('trim', split(',', $this->_propertyFilter['groupBy']));
        }
        $keys = empty($fields)? '': ', '.join(',', $fields);

        if (!isset($this->_propertyFilter['table'])) {

            $table = $this->getTable();

            if (empty($table)) {
               throw new BaseZF_DbCollection_Exception('property "table" must present in filter statement');
            } else {
                $this->filterTable($table);
            }
        }

        $query = 'SELECT COUNT(*) as cnt ' . $keys . ' FROM ' . $this->_propertyFilter['table'];

        if (!empty($this->_propertyFilter['where'])) {
            $query .= ' WHERE ' . $this->_propertyFilter['where'];
        }

        if (!empty($this->_propertyFilter['groupBy'])) {
            $query .= ' GROUP BY ' . $this->_propertyFilter['groupBy'];
        }

        return array(
            'query'  => $query,
            'fields' => $fields,
        );
    }

    /**
     * Get rocords count for filter
     *
     * @return int nb results for current filters
     */
    public function filterCount($cacheKey = null)
    {
        $data = $this->_getFilterCountQueryAndFields();
        $query = $data['query'];
        $fields = $data['fields'];

        try {
            $this->log('filterExecute query : ' . $query);

            $cacheKey = $this->getFilterCacheKey($query);

            $data = $this->QueryExecute($query, $cacheKey, array_merge(array('cnt'),$fields), $this->isRealTime(), $this->_cacheExpire);

            if ( empty($data) ) {
                return 0;
            }

            $count = array();
            if (empty($fields)) {
                if($row = array_shift($data)) {
                    $count = intval($row['cnt']);
                }
            } elseif( count($fields) > 1) {
                $primaryKey = array_shift($fields);
                foreach ($data as $row) {
                    $count[$row[$primaryKey]] = intval($row['cnt']);
                }
            } else {
                $count = $data;
            }

        // if no results found
        } catch (BaseZF_DbQuery_Exception $e) {
            throw new BaseZF_DbCollection_Exception('Can not calculate count of records');
        }
        return $count;
    }

    /**
     * Delete records using filter params
     *
     * @return this
     */
    public function filterDelete()
    {
        $this->_getFilterQuery();

        $query = 'DELETE FROM ' . $this->_propertyFilter['table'];

        if (!empty($this->_propertyFilter['where'])) {
            $query .= ' WHERE ' . $this->_propertyFilter['where'];
        }

        try {
            $db = $this->_getDbInstance();
            $stmt = $db->getStatement($query);
            $stmt->execute();
        } catch (BaseZF_DbQuery_Exception $e) {
            throw new BaseZF_DbCollection_Exception('can\'t delete records');
        }

        $this->clearCache();

        return $this;
    }

    public function filterReset()
    {
        $this->_propertyFilter = array();
        $this->_params = array();

		return $this;
    }
}
