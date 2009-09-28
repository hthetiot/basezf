<?php
/**
 * DbItem class in /BaseZF
 *
 * @category   BaseZF
 * @package    BaseZF_DbItem
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

abstract class BaseZF_DbItem
{
    /**
     * Define the zend log priority
     */
    const LOG_PRIORITY = 10;

    /**
     * Encrypt ids key
     */
    const EXTENTED_ID_INCREMENT = '14041985';

    /**
     * Unique Id
     */
    protected $_id = 0;

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
     * Reference to static instance cache according current table
     */
    protected $_instances = array();

    /**
     * Static instance cache
     */
    protected static $_STATIC_INSTANCES = array();

    /**
     * Data
     */
    protected $_data = array();

    /**
     * Realtime do not use cache
     */
    protected $_realtime = false;

    /**
     * Cache Key Template used by DbQuery Class
     */
    const _CACHE_KEY_TEMPLATE = '__id__';

    /**
     * array of modified properties
     */
    protected $_modified = array();

    /**
     * to check or not is new value same as old
     */
    protected $_checkValueSame = true;

    /**
     * Define if item is deleted
     */
    private $_isDeleted = false;

    /**
     * Define if data of item is loaded
     */
    private $_isLoaded = false;

    /**
     * Array for property dependency
     */
    protected $_dependency = array();

    //
    // DbCollection relation
    //

    protected $_collections = array();

    //
    // Constructor
    //

    /**
     * Constructor
     *
     * @param void $id unique object id
     * @param boolean $realtime disable cache
     */
    final protected function __construct($table, $id = null, $realtime = false)
    {
        $this->_table = $table;
        $this->loadStructure($table);

        $this->_createInstances();

        $this->setRealTime($realtime);
        $this->setId($id);

        $this->log('Create DbItem Instance: ' . $this);
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
     * Get dbItem class name
     *
     * @return string dbItem classname
     */
    protected static function _getItemClassName($table, $classBase = __CLASS__)
    {
        $classItem = $classBase . '_' . implode('_', array_map('ucfirst', explode('_', $table)));

        try {

            Zend_Loader::loadClass($classItem);

            if (!class_exists($classItem, true)) {
                throw new Exception('not existing class '. $classItem);
            }

            return $classItem;

        } catch (Exception $e) {

            return $classBase;
        }
    }

    //
    // Singleton Item per Table/Id
    //

    /**
     * Get instance of allready contructed object
     *
     * @param void $id unique object id
     * @param string $class item className
     *
     * @return BaseZF_DbItem object instance
     */
    public static function getInstance($table, $id = null, $realtime = false, $class = null)
    {
        if (empty($table)) {
           throw new BaseZF_DbItem_Exception('There no table name for BaseZF_DbItem' );
        }

        if (!is_null($id) && ($item = self::getExistInstance($table, $id))) {

            $item->setRealTime($realtime);
            $item->log('Get DbItem Instance: ' . $item);

        } else {

            $class = self::_getItemClassName($table, $class);

            $item = new $class($table, $id, $realtime);
            $item->log('Init DbItem Instance with table: ' . $table);
        }

        return $item;
    }

    final public static function getExistInstance($table, $id)
    {
        if (!empty($id) && isset(self::$_STATIC_INSTANCES[$table]['items'][$id])) {
            $item = &self::$_STATIC_INSTANCES[$table]['items'][$id];
        } else {
            $item = false;
        }

        return $item;
    }

    final protected function _getInstances()
    {
        if (empty($this->_instances)) {
            $this->_createInstances();
        }

        return $this->_instances;
    }

    final protected function _createInstances()
    {
        $this->_instances = &self::$_STATIC_INSTANCES[$this->getTable()];
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
     * Set unique id
     *
     * @param void $id unique DbObject id
     *
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    final protected function setId($id)
    {
        $oldId = $this->getId();

        if (!is_null($id) && !is_numeric($id)) {
            $id = self::getIdFromExtendedId($id);
        }

        // WARNING: we have to do this for two reasons:
        //  1. the mysql_fetch_row() always return string values
        //  2. some DbObject class doesn't use numerical primary key, so
        //     we have to ensure the key is numeric before to cast it
        if (is_numeric($id)) {
            $id = (int) $id;
        }

        $this->_id = $id;

        if ($oldId != $id) {
            if (!empty($oldId))
                unset($this->_instances['items'][$oldId]);
            if (!empty($id))
                $this->_instances['items'][$id] = $this;
        }

        return $this;
    }

    /**
     * Getter for unique id of DbObject
     *
     * @return void $this->_id value
     */
    final public function getId()
    {
        return $this->_id;
    }

    /**
     * Get original unique id from extended id string
     *
     * @param string $id extended id
     * @return void unique id
     */
    final static public function getIdFromExtendedId($id)
    {
        if (is_numeric($id)) {
            return $id;
        }

        // if id start with an x, it's an ASCII id, we have to decode it
        if (preg_match('/^x(.*)$/', $id, $m)) {
            $id = base_convert($m[1], 36, 10) - self::EXTENTED_ID_INCREMENT;
        } else {
            return null;
        }

        return $id;
    }

    /**
     * Generate extended id from original unique id
     *
     * @return string extended id generated by conversion of id to base 36
     */
    final static public function getDbItemExtendedId($id)
    {
        return 'x' . base_convert($id + self::EXTENTED_ID_INCREMENT, 10, 36);
    }

    /**
     * Generate extended id from original unique id
     *
     * @return string extended id generated by conversion of id to base 36
     */
    final public function getExtendedId()
    {
        return self::getDbItemExtendedId($this->getId());
    }

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
     * Get Primary Key
     *
     * @return string primary key field name
     */
    final public function getPrimaryKey()
    {
        return $this->_structure['primary'];
    }

    /**
     * Get type of field.
     *
     * @param string field name
     */
    final public function getFieldType( $field )
    {
        return isset($this->_structure['fields'][$field]) ? $this->_structure['fields'][$field] : false;
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
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    final public function setRealTime($realtime = true)
    {
        $this->_realtime = $realtime;
        if ($realtime) {
            $this->_data = array();
        }
        return $this;
    }

    /**
     * Get is item is deleted
     *
     * @return bool true if enable
     */
    final public function isDeleted()
    {
        return $this->_isDeleted;
    }

    final protected function _setDeleted($value = true)
    {
        $this->_isDeleted = $value;
        return $this;
    }

    /**
     * Get is item is deleted
     *
     * @return bool true if enable
     */
    final public function isLoaded()
    {
        return $this->_isLoaded;
    }

    final protected function _setLoaded($value = true)
    {
        $this->_isLoaded = $value;
        return $this;
    }

    /**
     * Get is any property is modified
     *
     * @return bool true if enable
     */
    final public function isModified()
    {
        return !empty($this->_modified);
    }

    //
    // Properties Func
    //

    public function getProperty($property)
    {
        if ($this->isPropertyModified($property))
           return $this->_modified[$property];

        if (!$this->isPropertyLoaded($property))
            $this->_loadProperty($property);

        if (!$this->isPropertyLoaded($property)) {
            throw new BaseZF_DbItem_Exception('No property available: "' . $this->getTable() . ':' . $property . '" for DbItem with id=' . $this->getId());
        }

        return $this->_data[$property];
    }

    public function getProperties(array $properties = null)
    {
        if (is_null($properties)) {
            $properties = array_merge(array_keys($this->_structure['fields']), array_keys($this->_data));
        }

        $propertiesValues = array();
        foreach ($properties as $property) {
            $propertiesValues[$property] = $this->getProperty($property);
        }

        return $propertiesValues;
    }

    public function setProperty($property, $value)
    {
        if (!isset($this->_structure['fields'][$property])) {
            throw new BaseZF_DbItem_Exception('Unable to set value to property "' . $this->getTable() . ':' . $property . '". Property is not found in structure.');
        }

        if ($this->_checkValueSame && $this->isPropertyLoaded($property) && $value === $this->_data[$property]) return $this;

        // empty string value is null value;
        if (mb_strlen(trim($value)) == 0) {
            $value = null;
        }

        $this->validate($property, $value);
        $this->_modified[$property] = $value;
        $this->_flushDependency($property);

        // clean property by types
        $this->_propertyToDbItemFormat($property, $this->_modified);

        return $this;
    }

    public function setProperties($data)
    {
        if (!is_array($data)) {
            throw new BaseZF_DbItem_Exception('Unable to data to item: data is not an array');
        }

        $exceptions = array();
        foreach ($data as $property => $value) {

            try {
                $this->setProperty($property, $value);
            } catch (BaseZF_DbItem_Exception $e) {
                $exceptions[] = $e->getMessage();
            }
        }

        if (!empty($exceptions)) {
            throw new BaseZF_DbItem_Exception(implode(', ', $exceptions));
        }
        return $this;
    }

    public function setVirtualProperty($property, $value, $propertyDependency = null) {

        if (isset($this->_structure['fields'][$property])) {
            throw new BaseZF_DbItem_Exception('Unable to set value to virtual property "' . $this->getTable() . ':' . $property . '". Field with same name exists in database.');
        }

        $this->_data[$property] = $value;
        if (!is_null($propertyDependency)) {
            $this->_addDependency($propertyDependency, $property);
        }
    }

    /**
     * Get a property, call the correct method to retrieve it
     *
     * @param string $property name of requested property
     *
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    protected function _loadProperty($property)
    {
        if (!$this->isPropertyLoaded($property) && !empty($this->_id)) {

            $ids = $this->_getIdsNeedToLoad($property, array($this->_id));

            $this->_massLoadProperty($ids, $property);
        }

        return $this;
    }

    protected function _getIdsNeedToLoad($property, $prefereIds = array(), $limit = BaseZF_DbCollection::MAX_ITEM_BY_REQUEST)
    {
        $ids = array_unique(array_merge($prefereIds, array_keys($this->_instances['items'])));
        $result = array();

        foreach ($ids as $id) {
            $item = isset($this->_instances['items'][$id]) ? $item = $this->_instances['items'][$id] : false;
            if (!$item || !$item->isPropertyLoaded($property)) {
                $result[] = $id;
                $limit--;
                if ($limit <= 0) break;
            }
        }

        return $result;
    }

    protected function _massLoadProperty($ids, $property)
    {
        if (empty($ids) || !is_array($ids)) {
            return $this;
        }

        try {

            // load from db or cache
            $data = $this->_loadData($ids);
            foreach ($data as $id => $row) {

                if (isset($this->_instances['items'][$id])){
                    $item = $this->_instances['items'][$id];
                    $item->_setData($row, true);
                }

            }

        } catch (Exception $e) {
            throw new BaseZF_DbItem_Exception('Unable load item properties from table "' . $this->getTable() . '" cause: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Check if a property allready loaded into $this->_data array
     *
     * @param string $property name of property
     *
     * @return bool true if available else false
     */
    final public function isPropertyLoaded($property)
    {
        return array_key_exists($property, $this->_data);
    }

    public function unloadProperty($property)
    {
        unset($this->_data[$property]);

        $this->_setLoaded(false);
    }

    public function resetProperty($property)
    {
        unset($this->_modified[$property]);
    }

    /**
     * Get is specified property modified
     *
     * @return bool true if enable
     */
    final public function isPropertyModified($property)
    {
        return array_key_exists($property, $this->_modified);
    }

    //
    // Property dependency
    //

    /**
     * Add property dependency, flush property depend when property master updated
     *
     * @param string $property
     * @param string $propertyDepend
     *
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    final protected function _addDependency($property, $dependProperty)
    {
        if (!isset($this->_dependency[$property])) {
            $this->_dependency[$property] = array();
        }
        if (!in_array($dependProperty, $this->_dependency[$property])) {
            $this->_dependency[$property][] = $dependProperty;
        }
        return $this;
    }

    /**
     * Flush property value if depend another property value
     *
     * @param object $item instance of item
     * @param string $property property name
     *
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    final protected function _flushDependency($property)
    {
        if (isset($this->_dependency[$property])) {
            foreach ($this->_dependency[$property] as $dependProperty) {
                unset($this->_data[$dependProperty]);
            }
        }

        return $this;
    }

    //
    // Validators callback methods
    //

    /**
     * Try to call validator method for a property
     *
     * @param string $property name of property to validate
     */
    final protected function validate($property, $value = null)
    {
        $camelCaseProperty = str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
        $methodName = 'get' . $camelCaseProperty . 'Validator';
        if (is_callable(array($this, $methodName))) {

            $validator = call_user_func(array($this, $methodName));

            // set id of dbobject for BaseZF_Framework_Validate_DbItem_Abstract validator
            if ($validator instanceof BaseZF_Framework_Validate_DbItem_Abstract && isset($this->_id)) {
                $validator->setDbItemId($this->_id);
            }

            if (!$validator->isValid($value)) {
                throw new BaseZF_DbItem_Exception('Unable to validate property "' . $property . '" cause: ' . implode(', ', $validator->getMessages()));
            }
        }

        return $this;
    }

    //
    // CacheKey Id
    //

    protected function _getCacheKey($id = null, $table = null)
    {
        if (is_null($id)) $id = $this->getId();
        if (is_null($table)) $table = $this->getTable();

        return 'dbItem_' . $table . '_' . $id ;
    }

    //
    // Select
    //

    protected function _getQuery()
    {
        $select = $this->_getDbInstance()->select();

        $select->from($this->getTable())
                  ->reset('columns')
               ->columns($this->_structure['values'])
               ->where($this->getPrimaryKey() . ' IN(:' . $this->getPrimaryKey() . ')');

        $query = $select->assemble();

        // free dbSelect Instance
        unset($select);

        return $query;
    }

    protected function _loadData($ids, $realTime = null, $cacheExpire = BaseZF_DbQuery::EXPIRE_NEVER)
    {
        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $logger = $this->_getLoggerInstance();

        $primaryKey = $this->getPrimaryKey();
        $query = $this->_getQuery();
        $fields = array_keys($this->_structure['fields']);
        $cacheKeyTemplate = $this->_getCacheKey(self::_CACHE_KEY_TEMPLATE);

        if ($realTime === null) {
            $realTime = $this->isRealTime();
        }

        // new dbQuery
        $dbQuery = new BaseZF_DbQuery($query, $cacheKeyTemplate, $db, $cache, $logger);
        $dbQuery->setQueryFields($fields);
        $dbQuery->setCacheExpire($cacheExpire);
        $dbQuery->setRealTime($realTime);
        $dbQuery->bindValue($primaryKey, $ids);
        $dbQuery->setCacheKeyByRows( $primaryKey, self::_CACHE_KEY_TEMPLATE );

        try {

            $dbQuery->execute();
            $data = $dbQuery->fetchAll();

        } catch (BaseZF_DbQuery_Exception_NoResults $e) {
            $data = array();
        }

        // free dbQuery Instance
        unset($dbQuery);

        return $data;
    }

    /**
     * Merge object data with new data
     *
     * @param array $data - record as assotiative array
     * @param boolean $isLoaded - is loaded from database or no
     *
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    protected function _setData($data, $isLoaded=false)
    {
        if (!is_array($data)) {
            throw new BaseZF_DbItem_Exception('Unable to merge data to item: data is not an array');
        }

        // clean property by types
        foreach ($data as $property => $value) {
            $this->_propertyToDbItemFormat($property, $data);
        }

        $this->_data = array_merge($this->_data, $data);

        if ($isLoaded) {
            $this->_setLoaded();
        }

        return $this;
    }

    protected function _propertyToDbItemFormat($property, &$data)
    {
        if (
            !($type = $this->getFieldType($property)) ||
            !isset($data[$property]) ||
            mb_strlen($data[$property]) == 0
        ) {
            return $this;
        }

        $value = $data[$property];

        // clean array
        if (strstr($type,'[]') == '[]' && !is_array($value)) {
            $data[$property] = self::str2arr($value);
        }

        // clean timestamp
        if (($type == 'TIMESTAMP' || $type == 'DATE') && !is_numeric($value)) {
            $data[$property] = strtotime($value);
        }

        return $this;
    }

    protected function _propertyToDbFormat($property, &$data)
    {
        if (
            !($type = $this->getFieldType($property)) ||
            !isset($data[$property]) ||
            mb_strlen($data[$property]) == 0
        ) {
            return $this;
        }

        $value = $data[$property];

        // clean array
        if (strstr($type,'[]') == '[]' && is_array($value)) {
            $data[$property] = self::arr2str($value);
        }

        // clean timestamp
        if (($type == 'TIMESTAMP' || $type == 'DATE') && is_numeric($value)) {
            $data[$property] = date('Y-m-d H:i:s', $value);
        }

        return $this;
    }

    //
    // Insert
    //

    /**
     * Insert new record
     */
    public function insert()
    {
        // ignore not modified
        if (!$this->isModified()) return $this;

        if ($id = $this->_insert($this->_modified)) {

            $this->setId($id);
            $this->_setData($this->_modified);
            $this->_modified = array();
        }

        return $this;
    }

    /**
     * Mass insert of new records
     */
    public static function massInsert($items)
    {
        if (empty($items) || !is_array($items)) {
            return false;
        }

        reset($items);
        $current = current($items);

        try {

            foreach ($items as $item) {
                $item->insert();
            }

        } catch (Exception $e) {

            foreach ($items as $item) {
                $item->setId(null);
            }

            throw new BaseZF_DbItem_Exception('Unable insert items to table "' . $current->_table . '" cause: ' . $e->getMessage());
        }
        return true;
    }

    /**
     * Insert new record to database
     *
     * @param array $propertyies assotiative array of properties
     *
     * @throw BaseZF_DbItem_Exception
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    protected function _insert($properties)
    {
        if (empty($properties)) return false;

        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $primaryKey = $this->getPrimaryKey();

        try {

            // clean property by types
            foreach ($properties as $property => $value) {
                $this->_propertyToDbFormat($property, $properties);
            }

            // add row
            $db->insert($this->getTable(), $properties);

            // get id
            $id = ( $this->getFieldType($primaryKey) == 'SERIAL' ) ? $db->lastInsertId($this->getTable(), $primaryKey) : $properties[$primaryKey];

            // clear cache
            $cache->remove($this->_getCacheKey($id));

        } catch (Exception $e) {

            throw new BaseZF_DbItem_Exception('Unable insert item to table "' . $this->getTable() . '" cause: ' . $e->getMessage());
        }

        return $id;
    }

    //
    // Update
    //

    /**
     * Update record
     */
    public function update()
    {
        // ignore not modified
        if (!$this->isModified()) return $this;

        if ($this->_update($this->_id, $this->_modified)) {
            $this->_setData($this->_modified);
            $this->_modified = array();
        }

        return $this;
    }

    /**
     * Mass records update
     */
    final public static function massUpdate($items)
    {
        if (empty($items) || !is_array($items)) return false;

        reset($items);
        $current = current($items);

        $needUpdate = false;
        foreach ($items as $item) {
            if ($item->isModified()) {
                $needUpdate = true;
            }
        }

        if ($needUpdate) {

            try {

                foreach ($items as $item) {
                    $item->update(false);
                }

            } catch (Exception $e) {

                foreach ($items as $item) {
                    $item->unload();
                }

                throw $e;
            }
        }
        return true;
    }

    /**
     * Update modified record to database
     *
     * @param array $propertyies assotiative array of properties
     *
     * @throw BaseZF_DbItem_Exception
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    protected function _update($id, $properties)
    {
        if (empty($id)) return false;
        if (empty($properties)) return false;

        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $primaryKey = $this->getPrimaryKey();

        try {

            // clean property by types
            foreach ($properties as $property => $value) {
                $this->_propertyToDbFormat($property, $properties);
            }

            // update row
            $db->update($this->getTable(), $properties,  $primaryKey . ' = ' . $db->quote($id));

            // clear cache
            $cache->remove($this->_getCacheKey());

        } catch (Exception $e) {

            throw new BaseZF_DbItem_Exception('Unable update item in table "' . $this->getTable() . '" cause: ' . $e->getMessage());
        }

        return true;
    }

    //
    // Delete
    //

    /**
     * Delete record
     */
    public function delete()
    {
        $id = $this->_id;
        if (empty($id)) return $this;

        $this->_delete($id);
        $this->setId(null);

        foreach ($this->_collections as $collection) {
            $collection->removeId($id);
        }

        $this->_setDeleted();
        $this->__destruct();

        return $this;
    }

    /**
     * Mass records delete
     */
    final public static function massDelete($items)
    {
        if (empty($items) || !is_array($items)) return false;

        $ids = array();
        foreach ($items as $item) {
            $ids[] = $item->getId();
        }

        reset($items);
        $current = current($items);

        $current->_delete($ids);

        foreach ($items as $item) {

            $id = $item->getId();
            $item->setId(null);

            foreach ($item->_collections as $collection) {
                $collection->removeId($id);
            }

            $item->_setDeleted();
            $item->__destruct();
        }

        return true;
    }

    /**
     * Delete record from database
     *
     * @param integer $id unique key
     *
     * @throw BaseZF_DbItem_Exception
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    protected function _delete($ids)
    {
        if (empty($ids)) return false;

        $db = $this->_getDbInstance();
        $cache = $this->_getCacheInstance();
        $primaryKey = $this->getPrimaryKey();
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
            throw new BaseZF_DbItem_Exception('Unable delete item(s) from table "' . $this->getTable() . '" cause: ' . $e->getMessage());
        }

        return true;
    }

    //
    // Collections
    //

    /**
     * Set item collection
     *
     * @param object instance of BaseZF_DbCollection
     *
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    public function addCollection(BaseZF_DbCollection $collection)
    {
        //@TODO: relation to collections not used now, seems like we should delete
        //this function in future or add functionality to refresh colections if item is changed

        if (!in_array($collection, $this->_collections)) {
           $this->_collections[] = &$collection;
        }

        return $this;
    }

    /**
     * Set item collection
     *
     * @param object instance of BaseZF_DbCollection
     *
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    public function removeCollection(BaseZF_DbCollection $collection)
    {
        //@TODO: relation to collections not used now, seems like we should delete
        //this function in future or add functionality to refresh colections if item is changed

        $key = array_search($collection, $this->_collections);
        if ($key !== FALSE) {
           unset($this->_collections[$key]);
        }

        return $this;
    }


    //
    // Magick Func
    //

    /**
     * Can use issset on __get properties
     *
     * @param string $str
     *
     * @return boolean true if isset else false
     */
    public function __isset($property)
    {
        return array_key_exists($property,$this->_modified) || array_key_exists($property, $this->_data);
    }

    /**
     * Get a property, call the correct method to retrieve it and throw callback if isset
     *
     * @param string $property name of requested property
     *
     * @throw BaseZF_DbItem_Exception
     * @return mixed Property value
     */
    public function __get($property)
    {
        return $this->getProperty($property);
    }

    public function __set($property, $value)
    {
        $this->setProperty($property, $value);
    }

    //
    // Conversion func
    //

    public static function arr2str($value)
    {

        if (!is_array($value)) {
            return $value;
        }

        if (empty($value)) {
            return array();
        }

        $value = '{' . implode(',', $value) . '}';

        return $value;
    }

    public static function str2arr($value)
    {

        if (is_array($value)) {
            return $value;
        }

        $value = substr($value, 1,-1); // remove brakets {} from string "{1,2,3}"

        if (empty($value)) {
            return array();
        }

        $value = split(',', $value);

        return is_array($value) ? $value : array();
    }

    public static function toDbDate( $timestamp = null )
    {
        if ( $timestamp === null ) {
            $timestamp = time();
        }

        return date("Y-m-d H:i:s", $timestamp);
    }

    public static function reverseToDbDate($date)
    {
       $converter = new Zend_Date();
       $converter->add($date, 'yyy-MM-dd HH:mm:ss');
       $time = $converter->get(Zend_Date::TIMESTAMP);

       unset($converter);

       return $time;
    }

    public static function bit2arr($value)
    {
        $result = array();
        $value = intval($value);
        for($i=0; $i<32; $i++) {
            $key = 1<<$i;
            if ( ($value & $key) != 0) {
                $result[] =  $key ;
            }
        }
        return $result;
    }

    //
    // Tools
    //

    /**
     * Reset all modified properties of Item
     *
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    public function reset()
    {
        // clean some things when object is reused
        $this->_modified = array();
        return $this;
    }

    /**
     * unload loaded properties of Item
     *
     * @return BaseZF_DbItem this object instance for more fluent interface
     */
    public function unload()
    {
        // clean some things when object is reused
        $this->_data = array();
        $this->_setLoaded(false);
        return $this;
    }

    final public function exists()
    {
        if (empty($this->_id)) {
            return false;
        }

        $property = $this->getPrimaryKey();

        if (!$this->isPropertyLoaded($property)) {
            $this->_loadProperty($property);
        }

        return $this->isPropertyLoaded($property);
    }

    /**
     * Compare current dbobject instance with another
     *
     * @param object $object dbobject compare with current
     *
     * @return true if is the same object, else false
     */
    final public function isEqual($object)
    {
        if (get_class($object) != get_class($this)) {
            return false;
        }

        return $this->_table == $object->getTable() && $this->getId() == $object->getId();
    }

    /**
     * string builder called to display object has string
     */
    public function __toString()
    {
        return '[' . get_class($this) . '-' . spl_object_hash($this) . ']' . $this->getTable() . '::' . $this->getId();
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
        $id = $this->_id;
        $this->__construct($this->getTable(), null, $this->_realtime);
        $this->setId($id);
    }

    //
    // Destructor
    //

    /**
     * Destroy instance of object
     */
    public function __destruct()
    {
        if (!empty($this->_id)) {
            unset($this->_instances['items'][$this->_id]);
        }
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
            $logger->log('DbItem -> ' . $msg, self::LOG_PRIORITY);
        }
    }
}

