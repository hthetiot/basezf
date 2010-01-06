<?php
/**
 * BaseZF_Collection_Db_Abstract class in /BaseZF/Collection/Db
 *
 * @category  BaseZF
 * @package   BaseZF_Item
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

abstract class BaseZF_Collection_Db_Abstract extends BaseZF_Collection_Abstract
{
    /**
     * Db table associate to this item
     */
    protected $_table;

    /**
     * Bool for realtime data
     */
    protected $_realtime = false;

    /**
     * Default expiration cache TTL
     */
    protected $_cacheExpire = BaseZF_Item_Db_Query::EXPIRE_NEVER;

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
        $this->_setTable($table);

        parent::__construct($ids);

        $this->setRealTime($realtime);

        // init default filter
        $this->filterReset();
    }

    /**
     * Destroy instance of object
     */
    public function __destruct()
    {

    }

    /**
     * Call ItemClassName getInstance
     *
     * @param string item id of item should be added to collection
     *
     * @return object instance of Item
     */
    protected function _getItemInstance($id = null)
    {
        $itemClassName = $this->_getItemClassName();

        return call_user_func(array($itemClassName, 'getInstance'), $this->getTable(), $id, $this->isRealTime());
    }

    //
    // Cache and Db instance getter
    //

    /**
     * Retrieve the Zend_Db database conexion instance
     *
     * @return object Zend_Db instance
     */
    abstract protected function _getDbInstance();

    /**
     * Retrieve the Zend_Cache instance
     *
     * @return object Zend_Cache instance
     */
    abstract protected function _getCacheInstance();

    /**
     * Retrieve the Zend_Log logger instance
     *
     * @return object Zend_log instance
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
    // Some getter and setter
    //

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
        // if table not initialized
        if ($this->_table !== $table) {

            $this->_table = $table;

            // init default filter
            $this->filterReset();

            // clear ids
            $this->setIds(array());
        }

        return $this;
    }

    /**
     * Get Primary Key of a table throw is item class
     *
     * @param string $tableName
     *
     *
     * @return string primary key field name
     */
    final protected function getTablePrimaryKey($tableName = null)
    {
        static $primaryKeys = array();

        // if no table provided git current
        if (is_null($tableName)) {
            $tableName = $this->getTable();
        }

        // available from static $primaryKeys cache
        if (array_key_exists($tableName, $primaryKeys) !== false) {

            return $primaryKeys[$tableName];

        // if no item found in collection we create new one call getColumnPrimaryKey and destruct it
        } else  if (count($this->_ids) == 0) {

            $newItem = $this->_getItemInstance();
            $primaryKeys[$tableName] = $newItem->getColumnPrimaryKey();
            unset($newItem);

        // if item found in collection we get the first then call getColumnPrimaryKey
        } else {

            $item = $this->getItem($this->getRandomId());
            $primaryKeys[$tableName] = $item->getColumnPrimaryKey();
        }

        if (array_key_exists($tableName, $primaryKeys) === false) {
            $itemClassName = $this->_getItemClassName();
            throw new BaseZF_Collection_Db_Exception(sprintf('Unable found Item Db "%s" primary key for table "%s"', $itemClassName, $this->getTable()));
        }

        return $primaryKeys[$tableName];
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
               ->columns($this->getTablePrimaryKey());

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
    public function filterCache($expire = BaseZF_Item_Db_Query::EXPIRE_NEVER)
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

            $primaryKey = $this->getTablePrimaryKey();
            $fields = array($primaryKey);
            $dbQuery = $this->_getItemDbQuery($select->assemble(), $cacheKey, $fields);

            // exec query throw dbQuery
            $dbQuery->execute();

            // add ids to collection
            $ids = array();
            while ($row = $dbQuery->fetch()) {
                $ids[] = $row[$primaryKey];
            }

            $this->setIds($ids);

        // if no results found
        } catch (BaseZF_Item_Db_Query_Exception_NoResults $e) {
            $this->setIds(array());
        }

        // free dbQuery Instance
        unset($dbQuery);

        return $this;
    }

    /**
     * Retrieve the Db Select instance
     *
     * @return object instance of Zend_Db_Select
     */
    protected function _getDbSelectInstance()
    {
        static $select;

        if (!isset($select)) {
            $select = $this->_getDbInstance()->select();
        }

        return $select;
    }

    /**
     * Retrieve the Db Select count instance
     *
     * @return object instance of Zend_Db_Select
     */
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
            $dbQuery = $this->_getItemDbQuery($selectCount->assemble(), $cacheKey, $fields);

            // exec query throw dbQuery
            $dbQuery->execute();
            $data = $dbQuery->fetch();

            $results = (isset($data['nb']) ? $data['nb'] : 0);

        } catch (BaseZF_Item_Db_Query_Exception_NoResults $e) {

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

    final public function _getItemDbQuery($query, $cacheKey = null, array $fields = array())
    {
        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $logger = $this->_getLogInstance();

        // new dbQuery
        $dbQuery = new BaseZF_Item_Db_Query($query, $cacheKey, $db, $cache, $logger);
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

    //
    // Paginator manager
    //

    /**
     * Clear cache for query which uses paging
     *
     * @param interger $perPage
     * @param integer $recordCount
     */
    final public function clearPerPageCache($perPage = null, $recordCount = null)
    {
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

    final public function _updatePerPageCache(Zend_Db_Select $select)
    {
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
    final public function filterDelete()
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
    // DbItem manager
    //

    /**
     * Create a new item and associate it to collection
     *
     * @return DbItem object instance of new item
     */
    public function newItem($data)
    {
        $newItem = parent::newItem($data);

        // clear cache cause we add an item
        $this->clearCache();

        return $newItem;
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
            '_table',
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
}

