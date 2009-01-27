<?php
/**
 * DbQuery class in /BazeZF/
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_DbQuery
{
	/**
	 * Define the zend log priority
	 */
	const LOG_PRIORITY = 11;

    const EXPIRE_NONE 	= false;
    const EXPIRE_NEVER 	= null;
    const EXPIRE_MINUTE = 60;
    const EXPIRE_HOUR 	= 3600;
    const EXPIRE_DAY 	= 86400;
    const EXPIRE_WEEK 	= 604800;
    const EXPIRE_MONTH 	= 2592000;

    /**
     * Instance of Cache
     */
    protected static $_CACHE;

    /**
     * Instance of Db connexion
     */
    protected static $_DB;

    /**
     * Instance of Logger
     */
    protected static $_LOGGER;

    /**
     * SQL Query
     */
    protected $_query = null;

    /**
     * Query fields
     */
    protected $_queryFields = null;

    /**
     * Query cache key
     */
    protected $_cacheKey = null;

    /**
     * Expire cache period in seconds
     */
    protected $_expire = self::EXPIRE_DAY; // one day by default

    /**
     * Data to build cacheKey for each rows
     */
    protected $_cacheKeysByRows = array();

    /**
     * Binded values for pdo stmt
     */
    public $_bindValues = array();

    /**
     * True if a bind have a array has value
     */
    protected $_multipleBinding = false;

    /**
     * Data
     */
    protected $_data = array();

    /**
     * Set realtime to use db has unique source
     */
    protected $_realtime;

    /**
     * Use to detect firt fetch call
     */
    protected $_firtFetch = true;

    /**
     * Init QueryCache with new query
     *
     * @param string $query SQL query
     * @param string $cacheKey query cache key
     * @param object $db instance of Zend_Db
     * @param object $cache instance of BaseZF_Cache
     */
    public function __construct($query, $cacheKey = null, $db = null, $cache = null, $logger = null)
    {
        if (!is_null($query)) {
            $this->setQuery($query);
        }

        if (!is_null($cacheKey)) {
            $this->setCacheKey($cacheKey);
        }

        if (!is_null($db)) {
            $this->setDbInstance($db);
        }

        if (!is_null($cache)) {
            $this->setCacheInstance($cache);
        }

        if (!is_null($logger)) {
            $this->setLoggerInstance($logger);
        }
    }

    /**
     * Get $this->_query value
     *
     * @return string $this->_query value
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Set a new query to queryCache and reset binding
     *
     * @return $this for more fluent interface
     */
    public function setQuery($query)
    {
        $this->_query = $query;

        $this->_reset();

        return $this;
    }

    /**
     * Set a query fields to queryCache
     *
     * @return $this for more fluent interface
     */
    public function setQueryFields($queryFields)
    {
        $this->_queryFields = $queryFields;

        return $this;
    }

    /**
     * Get $this->_cacheKey value, build one if empty
     *
     * @return string a queryCache cache key used by memcache
     */
    public function getCacheKey()
    {
        if (is_null($this->_cacheKey)) {
            throw new BaseZF_DbQuery_Exception('Fatal: empty cachekey');
        }

        return $this->_cacheKey;
    }

    /**
     * Get $this->_expire value
     *
     * @return string a queryCache cache key used by memcache
     */
    public function getCacheExpire($expire)
    {
        return $this->_expire;
    }

    /**
     * Set $this->_expire value
     *
     * @return $this for more fluent interface
     */
    public function setCacheExpire($expire)
    {
        $this->_expire = $expire;

        return $this;
    }

    /**
     * Set $this->_cacheKey value
     *
     * @return $this for more fluent interface
     */
    public function setCacheKey($cacheKey)
    {
        $this->_cacheKey = $cacheKey;

        return $this;
    }

    /**
     * Define if object use cache or not
     *
     * @param bool $realtime set if realtime is enable or not
     *
     * @return $this for more fluent interface
     */
    public function setRealTime($realtime = true)
    {
        $this->_realtime = $realtime;

        return $this;
    }

    /**
     * Get is realtime is enable
     *
     * @return bool true if enable
     */
    public function isRealTime()
    {
        return $this->_realtime;
    }

    //
    // Cache and Db instance getter
    //

    /**
     * Retrieve the Db instance
     *
     * @return object instance db class value of self::$_DB
     */
    static protected function _getDbInstance()
    {
        return self::$_DB;
    }

    static public function setDbInstance($db)
    {
        self::$_DB = $db;
    }

    /**
     * Retrieve the Cache instance
     *
     * @return object instance of BaseZF_Cache_Interface value of self::$_CACHE
     */
    static protected function _getCacheInstance()
    {
        return self::$_CACHE;
    }

    static public function setCacheInstance($cache)
    {
        self::$_CACHE = $cache;
    }

    /**
     * Retrieve the Logger instance
     *
     * @return object instance of BaseZF_Cache_Interface value of self::$_LOGGER
     */
    static protected function _getLoggerInstance()
    {
	   return self::$_LOGGER;
    }

    static public function setLoggerInstance($logger)
    {
        self::$_LOGGER = $logger;
    }

    //
    // Statement backport
    //

    /**
     * Store binded value for query stmt
     *
     * @param string $binding binded variable into query
     * @param string $value value for variable binded into query
     *
     * @return $this for more fluent interface
     */
    public function bindValue($binding, $value)
    {
        if (is_array($value)) {
            $this->_multipleBinding = true;
        }

        $this->_bindValues[$binding] = $value;

        return $this;
    }

    /**
     * Build bind for bind value with array for query stmt and update query
     *
     * @return $this for more fluent interface
     */
    private function _buildMultipleBinding()
    {
        if (!$this->_multipleBinding) {
            return;
        }

        $queryBindings = array();

        foreach ($this->_bindValues as $bindingKey => $values) {

            if (!is_array($values)) {
                continue;
            }

            foreach ($values as $key => $value) {
                $binding = $bindingKey . '_' . $key;

                $this->bindValue($binding, $value);

                $queryBindings[] = ':' . $binding;
            }

            unset($this->_bindValues[$bindingKey]);

            // update query for multiple bindings
            $this->_query = str_replace(':' . $bindingKey, implode(', ', $queryBindings), $this->_query);
        }

        return $this;
    }

    /**
     * Init CacheKey by rows system
     *
     * @param string $field must be a bind Key and a field in query select
     * @param string $placeHolder placeHolder in cachekey must be replace by value
     */
    public function setCacheKeyByRows($field, $placeHolder = '__id__')
    {
        // check if field available in _bindValues
        if (!isset($this->_bindValues[$field])) {
            throw new BaseZF_DbQuery_Exception('Fatal: Missing cacheKeysByRows field in binded values');

        } else if (!is_array($this->_bindValues[$field])) {

            $this->_bindValues[$field] = array($this->_bindValues[$field]);
        }

        // check if field available in query fields
        if (!in_array($field, $this->_getQueryFields())) {
            throw new BaseZF_DbQuery_Exception('Fatal: Missing cacheKeysByRows field in query fields');
        }

        $this->_cacheKeysByRows = array
        (
            'place_holder'  => $placeHolder,
            'field'         => $field,
            'values'        => $this->_bindValues[$field],
        );

        return $this;
    }

    private function _getCacheKeyByRowsField()
    {
        if (!$this->_useCacheKeyByRows()) {
             throw new BaseZF_DbQuery_Exception('Fatal: Missing cacheKeysByRows values');
        }

        return $this->_cacheKeysByRows['field'];
    }

    private function _useCacheKeyByRows()
    {
        return count($this->_cacheKeysByRows) ? true : false;
    }

    private function _buildCacheKeysByRowsFromValues()
    {
        if (!$this->_useCacheKeyByRows()) {
             throw new BaseZF_DbQuery_Exception('Fatal: Missing cacheKeysByRows values');
        }

        $placeHolder = $this->_cacheKeysByRows['place_holder'];
        $values = $this->_cacheKeysByRows['values'];
        $cacheKey = $this->_cacheKey;

        $cacheKeys = array();
        foreach ($values as $value) {
            $cacheKeys[$value] = str_replace($placeHolder, $value, $cacheKey);
        }

        return $cacheKeys;
    }

    private function _buildCacheKeyByRowValue($value)
    {
        if (!$this->_useCacheKeyByRows()) {
            throw new BaseZF_DbQuery_Exception('Fatal: Missing cacheKeysByRows values');
        }

        $placeHolder = $this->_cacheKeysByRows['place_holder'];
        $cacheKey = $this->_cacheKey;
        $rowCacheKey = str_replace($placeHolder, $value, $cacheKey);

        return $rowCacheKey;
    }

    public function clear($cacheKey = null)
    {
        if (!$this->_useCacheKeyByRows()) {

            if(is_null($cacheKey)) {
                $cacheKey = $this->getCacheKey();
            }

            $this->_removeFromCache($cacheKey);

            $this->_data = array();

            $this->rewind();
        }
    }
    /**
     * Try to retrieve data from diferente source, cache and database
     *
     * @return array results from memcache of from stmt fetch
     */
    public function execute()
    {
        if ($this->_expire === BaseZF_DbQuery::EXPIRE_NONE) {
            $this->setRealTime(true);
        }

        if (!$this->_useCacheKeyByRows()) {
            $data = $this->_executeSimpleCacheKey();
        } else {
            $data = $this->_executeCacheKeysByRows();
        }

        $this->_data = $data;

        // reset data array position
        $this->rewind();
    }

    public function _executeSimpleCacheKey()
    {
        $queryFields = $this->_getQueryFields();
        $cacheKey = $this->getCacheKey();


        try {

            $data = array();

            if ($this->isRealTime()) {
                throw new BaseZF_DbQuery_Exception('realtime data requested');
            }

            // try to get from memcache
            $cacheData = $this->_getFromCache($cacheKey);

            // check cache integrity and build data
            foreach ($cacheData as $rowId => $cacheDataRow) {

                // build data value from cacheData
                $row = array_combine($queryFields, $cacheDataRow);

                if (!is_array($row)) {
                    throw new BaseZF_DbQuery_Exception('realtime data requested');
                }

                $data[$rowId] = $row;
            }

        } catch (BaseZF_DbQuery_Exception $e) {

            // build multiple binding with the reste of ids
            $this->_buildMultipleBinding();

            // try to get from db
            $stmt = $this->_getStatement($this->getQuery());

            // bind value to db stmt
            foreach ($this->_bindValues as $binding => $value) {
                $stmt->bindValue($binding, $value);
            }

            $stmt->execute();

            $i = 0;
            $data = array();
            $cacheData = array();
            while ($row = $stmt->fetch()) {

                // build cacheData value
                $cacheData[$i] = array();
                foreach ($queryFields as $k => $v) {
                    $cacheData[$i][] = $row[$v];
                }

                $data[$i] = (array) $row;
                $i++;
            }

            if (!$this->isRealTime()) {
                $this->_setInCache($cacheKey, $cacheData, $this->_expire);
            }

            if (empty($data)) {
                throw new BaseZF_DbQuery_Exception_NoResults('no data found for this request');
            }
        }

        return $data;
    }

    public function _executeCacheKeysByRows()
    {

		$queryFields = $this->_getQueryFields();
        $cacheKey = $this->getCacheKey();
        $cacheKeysByRows = $this->_buildCacheKeysByRowsFromValues();

        try {

            $data = array();
            $missingCacheKeys = $cacheKeysByRows;
            $missingIds = array();

            if ($this->isRealTime()) {
                throw new BaseZF_DbQuery_Exception('realtime data requested');
            }

            // try to get from memcache
            $cacheDataByRows = $this->_getFromCache($cacheKeysByRows);

            // check cache integrity and build data
            foreach ($cacheDataByRows as $cacheKeyByRow => $cacheDataByRow) {
                $id = array_search($cacheKeyByRow, $cacheKeysByRows);

                // check cache
                if (!is_array($cacheDataByRow) || count($cacheDataByRow) != count($queryFields)) {
                    unset($cacheDataByRows[$cacheKeyByRow]);
                    continue;
                }
                 // build data value from cacheData
                $data[$id] = array_combine($queryFields, $cacheDataByRow);
            }

            if (count($data) != count($cacheKeysByRows)) {

                $missingCacheKeys = array_diff($cacheKeysByRows, array_keys($cacheDataByRows));
                $missingIds = array_keys($missingCacheKeys);

                $this->bindValue($this->_getCacheKeyByRowsField(), $missingIds);

                $this->log('data with cache key(s) "' . implode(', ', $missingCacheKeys) . '" missing from cache');

                throw new BaseZF_DbQuery_Exception('data with cache key(s) "' . implode(', ', $missingCacheKeys) . '" missing from cache');
            }

        } catch (BaseZF_DbQuery_Exception $e) {

            // build multiple binding with the reste of ids
            $this->_buildMultipleBinding();

            // try to get from db
            $stmt = $this->_getStatement($this->getQuery());

            // bind value to db stmt
            foreach ($this->_bindValues as $binding => $value) {
                $stmt->bindValue($binding, $value);
            }

            $stmt->execute();

            $cacheDataByRows = array();
            while ($row = $stmt->fetch()) {

                $id = $row[$this->_getCacheKeyByRowsField()];
                $cacheKey = $this->_buildCacheKeyByRowValue($id);

                // build cacheData value
                $cacheDataByRows[$cacheKey] = array();
                foreach ($queryFields as $k => $v) {

                    if (!array_key_exists($v, $row)) {
                        throw new BaseZF_Exception('field "' . $v . '" not found in results'); // not BaseZF_DbQuery_Exception
                    }

                    $cacheDataByRows[$cacheKey][] = $row[$v];
                }

                // set Data value
                $data[$id] = (array) $row;
            }

            // store data in memCache
            if (!$this->isRealTime()) {
                foreach ($cacheDataByRows as $cacheKeyByRow => $cacheDataByRow) {
                    $this->_setInCache($cacheKeyByRow, $cacheDataByRow, $this->_expire);
                }
            }

        }

        if (empty($data)) {
            throw new BaseZF_DbQuery_Exception_NoResults('no data found for this request');
        }

        return $data;
    }

    //
    // Implement SeekableIteratory and Countable
    //

    public function rewind()
    {
       reset($this->_data);
       $this->_firtFetch = true;
    }

    public function current()
    {
       $var = current($this->_data);

       return $var;
    }

    public function key()
    {
       $var = key($this->_data);

       return $var;
    }

    public function next()
    {
       $var = next($this->_data);
       $this->_firtFetch = false;

       return $var;
    }

    public function valid()
    {
       $var = $this->current() !== false;

       return $var;
    }

    public function seek($index)
    {
        if (!isset($this->_data[$index])) {
            throw new BaseZF_DbQuery_Exception('Unable to seek on index "' . $index . '"');
        }

        array_set_current($this->_data, $index);

        return $this->current();
    }

    public function count()
    {
        return count($this->_data);
    }

    public function fetch()
    {
        if (!$this->_firtFetch)
        {
            return $this->next();
        }

        $this->_firtFetch = false;

        return $this->current();
    }

    public function fetchAll()
    {
        return $this->_data;
    }

    //
    // Query Parsing
    //

    /**
     * Retreive selected fields in current sql query
     *
     * @return array fields requested in current query
     */
    private function _getQueryFields()
    {
        if( !$this->_queryFields ) {
            //parse query if field list is not specified
            $this->_queryFields = $this->_parseQueryFields();
        }
        return $this->_queryFields;
    }

    /**
     * parse query and return list of fields
     *
     * @return array fields requested in current query
     */
    private function _parseQueryFields()
    {
        $query = preg_replace("|\s+|"," ",$this->_query);
        $pattern = "|SELECT(?: DISTINCT)? (.+) FROM |i";

        if(!preg_match($pattern,$query,$fields)) return false;

        $fields = array_map('trim', explode(',', $fields[1]));
        $pattern = "/((?:.+(?: AS (?P<alias>\w+)))|(?:(?P<just_field>.+)))/i";
        foreach ($fields as &$field) {
            preg_match($pattern,$field,$match);
            $field = empty($match['alias']) ? $match['just_field'] : $match['alias'];
        }

        return $fields;
    }

    //
    // Cache source
    //

    /**
     * Retrieve something from cache
     *
     * @throw BaseZF_Dbobject_Exception
     * @param string $cacheKey Key in cache
     * @param object $cache use a specific instance of cache
     *
     * @return mixed data from cache
     */
    protected function _getFromCache($cacheKey, $cache = null)
    {
        if (is_null($cache)) {
            $cache = $this->_getCacheInstance();
        }

        if (!is_array($cacheKey)) {

            if(!$value = $cache->load($cacheKey)) {
                throw new BaseZF_DbQuery_Exception('value for cache key "' . $cacheKey . '" not found');
            }

        } else {

            $value = array();

            foreach ($cacheKey as $cacheKeyName) {
                $value[$cacheKeyName] = $cache->load($cacheKeyName);
            }

        }

        // use json_decode if key contain json

        // if multiple results
        if (is_array($value)) {
            $keys = array_keys($value);

            if (strpos(current($keys), 'json')) {
                $value = array_map('json_decode', $value);
            }

        // one results
        } elseif (strpos($cacheKey, 'json')) {
            $value = json_decode($value);
        }

        return $value;
    }

    /**
     * Set something in cache
     *
     * from prototype: bool Memcache::set ( string key, mixed var [, int flag [, int expire]] )
     *
     * @param string $cacheKey Key in cache
     * @param mixed $value Object to set in cache
     * @param integer $flag Use MEMCACHE_COMPRESSED to use zlib
     * @param integer $expire Expiration time of the key
     * @param object $cache use a specific instance of cache
     *
     * @return boolean true if success, else false
     */
     protected function _setInCache($cacheKey, $value, $expire = self::EXPIRE_NONE, $cache = null)
    {
        if (is_null($cache)) {
            $cache = $this->_getCacheInstance();
        }

        // add json_encode if array and key use "json"
        if (is_array($value) && strpos($cacheKey, 'json')) {
            $value = json_encode($value);
        }

        return $cache->save($value, $cacheKey); //, array(), $expire);
    }

    protected function _removeFromCache($cacheKey, $cache = null)
    {
        if (is_null($cache)) {
            $cache = $this->_getCacheInstance();
        }

        return $cache->remove($cacheKey);
    }

    //
    // DB source
    //

    /**
     * Prepare, cache and return a SQL statement
     *
     * @param string $query SQL query
     * @todo add getStmt on pdo adpater
     *
     * @return object a new statement of a old if allready called
     */
    final protected function _getStatement($query, $db = null)
    {
        if (is_null($db)) {
            $db = $this->_getDbInstance();
        }

        // check for named binding
        if ($db->supportsParameters('named') === false) {
            throw new BaseZF_DbQuery_Exception(get_class($db) . ' do not support named parameters use PDO adapter.');
        }

        $stmtClass = $db->getStatementClass();
        $stmt = new $stmtClass($db, $query);

        return $stmt;
    }

    //
    // Data Management
    //

    /**
     * Reset object properties from new query
     *
     * @return $this for more fluent interface
     */
    protected function _reset()
    {
        $this->_cacheKey = null;

        $this->_cacheKeyByRows = array();

        $this->_firtFetch = true;

        $this->_bindValues = array();

        $this->_queryFields = null;

        return $this;
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
            $logger->log('DbQuery -> ' . $msg, self::LOG_PRIORITY);
        }
    }
}

