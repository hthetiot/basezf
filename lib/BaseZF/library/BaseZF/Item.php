<?php
/**
 * DbItem class in /BaseZF
 *
 * @category   BaseZF
 * @package    BaseZF_Item
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *             Oleg Stephanwhite (oleg)
 *             Fabien Guiraud (fguiraud)
 */

abstract class BaseZF_DbItem implements ArrayAccess
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
     * Encrypt ids preffix
     */
    const EXTENTED_ID_PREFFIX = 'x';

    /**
     * Unique Id
     */
    protected $_id = null;

    /**
     * Static instance cache
     */
    protected static $_INSTANCES = array();

    /**
     * Array of properties values
     */
    protected $_data = array();

    /**
     * Array of modified properties
     */
    protected $_modified = array();

    /**
     * To check or not is new value same as old on update
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
    protected function __construct($id = null)
    {
        $this->setId($id);

        $this->log('Create Item Instance: ' . $this);
    }

    //
    // Cache and Db instance getter
    //

    /**
     * Retrieve the Logger instance
     */
    abstract protected function _getLoggerInstance();

    //
    // Class Names getter
    //

    /**
     * Get dbItem class name
     *
     * @return string dbItem classname
     */
    protected static function _getItemClassName($table, $classBase = __CLASS__)
    {
        $classItem = $classBase . '_' . implode('_', array_map('ucfirst', explode('_', $table)));

        try {

            @Zend_Loader::loadClass($classItem);

            if (!class_exists($classItem, true)) {
                throw new BaseZF_DbItem_Exception('not existing class '. $classItem);
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
           throw new BaseZF_DbItem_Exception('There no table name for BaseZF_DbItem');
        }

        if (!$item = self::getExistInstance($table, $id)) {
            $item = self::_createInstance($table, $id, $class);
        }

        $item->setRealTime($realtime);

        return $item;
    }

    final public static function getExistInstance($table, $id)
    {
        if (!empty($id) && isset(self::$_INSTANCES[$table][$id])) {

            $item = &self::$_INSTANCES[$table][$id];
            $item->log('Get DbItem Instance: ' . $item);

        } else {

            $item = false;
        }

        return $item;
    }

    final protected static function _createInstance($table, $id, $class = null)
    {
        $class = self::_getItemClassName($table, $class);

        $item = new $class($table, $id);
        $item->log('Init DbItem Instance with table: ' . $table);

        return $item;
    }

    final protected static function _saveInstance($table, $id, $item)
    {
        if(!empty($id)) {

            if (!isset(self::$_INSTANCES[$table])) {
                self::$_INSTANCES[$table] = array();
            }

            if (isset(self::$_INSTANCES[$table][$id])) {
                 throw new BaseZF_DbItem_Exception(sprintf('Unable to save duplicate instance for id "%d" used table "%s"', $id, $table));
            }

            self::$_INSTANCES[$table][$id] = &$item;
        }

        return $item;
    }

    final public static function destructExistInstance($table, $id)
    {
        if ($deleted = isset(self::$_INSTANCES[$table][$id])) {
            unset(self::$_INSTANCES[$table][$id]);
        }

        return $deleted;
    }

    final protected function _getInstances()
    {
        return self::$_INSTANCES[$this->getTable()];
    }

    //
    // Some getter and setter
    //

    /**
     * Set unique id
     *
     * @param void $id unique DbObject id
     *
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
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

            if (!empty($oldId)) {
                self::destructExistInstance($this->getTable(), $oldId);
            }

            if (!empty($id)) {
                self::_saveInstance($this->getTable(), $id, $this);
            }
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
        $matches = array();
        if (preg_match('/^' . self::EXTENTED_ID_PREFFIX . '(.*)$/', $id, $matches)) {
            $id = base_convert($matches[1], 36, 10) - self::EXTENTED_ID_INCREMENT;
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
        return self::EXTENTED_ID_PREFFIX . base_convert($id + self::EXTENTED_ID_INCREMENT, 10, 36);
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

    //
    // State setter and getter
    //

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

    /**
     * Get property or virtual property value
     *
     * @param string $property name of requested property
     *
     * @return void property value
     */
    public function getProperty($property)
    {
        if ($this->isPropertyModified($property)) {
           return $this->_modified[$property];
        } else if (!$this->isPropertyLoaded($property)) {
            $this->_loadProperty($property);
        } else if (!$this->isPropertyLoaded($property)) {
            throw new BaseZF_DbItem_Exception('No property available: "' . $this->getTable() . ':' . $property . '" for DbItem with id=' . $this->getId());
        }

        return $this->_data[$property];
    }

    /**
     * Get properties or virtual properties values
     *
     * @param array $properties names of requested properties
     *
     * @return array an array assoc of property value indexed by property name
     */
    public function getProperties(array $properties = array())
    {
        // load all property values if none given
        if (empty($properties)) {
            $properties = $this->getAvailableProperties();
        }

        $propertiesValues = array();
        foreach ($properties as $property) {
            $propertiesValues[$property] = $this->getProperty($property);
        }

        return $propertiesValues;
    }

    public function getAvailableProperties()
    {
        return array_merge(array_keys($this->_data));
    }

    /**
     * Set value of a non virtual property
     *
     * @param string $property name of updated property
     * @param void $value value of updated property
     *
     */
    public function setProperty($property, $value)
    {
        if (!$this->getFieldType($property)) {
            throw new BaseZF_DbItem_Exception('Unable to set value to property "' . $this->getTable() . ':' . $property . '". Property is not found in structure.');
        }

        // check same property
        if ($this->_checkValueSame && $this->isPropertyLoaded($property) && $value === $this->_data[$property]) {
            return $this;
        }

        // empty string value is null value by default
        if (mb_strlen(trim($value)) == 0) {
            $value = null;
        }

        $this->validate($property, $value);
        $this->_modified[$property] = $value;
        $this->_flushDependency($property);

        // clean property by types
        $this->_propertyToDbItemFormat($property, $this->_modified);

        return $this->getProperty($property);
    }

    /**
     * Set value of a non virtual properties
     *
     * @param array $data array an array assoc of property value indexed by property name
     *
     */
    public function setProperties(array $data)
    {
        if (!is_array($data)) {
            throw new BaseZF_DbItem_Exception('Unable to data to item: data is not an array');
        }

        foreach ($data as $property => $value) {
            $this->setProperty($property, $value);
        }

        return $this->getProperties(array_keys($data));
    }

    /**
     * Set value of a virtual properties
     *
     * @param string $property name of updated property
     * @param mixed $value value of updated property
     * @param mixed $propertyDependency value of dependency property
     *
     */
    public function setVirtualProperty($property, $value, $propertyDependency = null)
    {
        if ($this->getFieldType($property)) {
            throw new BaseZF_DbItem_Exception('Unable to set value to virtual property "' . $this->getTable() . ':' . $property . '". Field with same name exists in database.');
        }

        $this->_data[$property] = $value;

        if (!is_null($propertyDependency)) {
            $this->_addDependency($property, $propertyDependency);
        }

        return $this->getProperty($property);
    }

    /**
     * Get a property, call the correct method to retrieve it
     *
     * @param string $property name of requested property
     *
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    protected function _loadProperty($property)
    {
        if (!$this->isPropertyLoaded($property) && !empty($this->_id)) {

            $ids = $this->_getIdsNeedToLoad($property, array($this->_id));

            $this->_massLoadProperty($ids, $property);
        }

        return $this;
    }

    /**
     * @todo use related dbcollection
     */
    protected function _getIdsNeedToLoad($property, $prefereIds = array(), $limit = BaseZF_DbCollection::MAX_ITEM_BY_REQUEST)
    {
        // get neighbours ids
        $neighboursItems = $this->_getNeighboursItems();

        $ids = array_unique(array_merge($prefereIds, array_keys($neighboursItems)));
        $result = array();

        foreach ($ids as $id) {

            $item = (isset($neighboursItems[$id]) ? $item = $neighboursItems[$id] : false);

            if (!$item || !$item->isPropertyLoaded($property)) {

                $result[] = $id;
                $limit--;

                if ($limit <= 0) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @todo remove link to db for more abstract item class
     */
    final protected function _massLoadProperty($ids, $property)
    {
        if (empty($ids) || !is_array($ids)) {
            return $this;
        }

        try {

            // load from db or cache
            $data = $this->_loadData($ids);
            foreach ($data as $id => $row) {
                if ($item = self::getExistInstance($this->getTable(), $id)) {
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

    /**
     * Unload property from database or modified
     *
     * @param string $property name of property
     */
    final public function unloadProperty($property)
    {
        // clear modified if exist
        if ($this->isPropertyModified($property)) {
            $this->resetProperty($property);
        }

        // clear loaded if exist
        if ($this->isPropertyLoaded($property)) {
            unset($this->_data[$property]);
            $this->_setLoaded(false);
        }

        return $this;
    }

    /**
     * Get is specified property modified
     *
     * @param string $property name of property
     */
    final public function isPropertyModified($property)
    {
        return array_key_exists($property, $this->_modified);
    }

    /**
     * Reset a modifed property
     *
     * @param string $property name of property
     */
    final public function resetProperty($property)
    {
        if ($this->isPropertyModified($property)) {
            unset($this->_modified[$property]);
        }

        return $this;
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
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    final protected function _addDependency($property, $dependProperty)
    {
        // try to get $dependProperty
        $this->getProperty($dependProperty);

        if (!isset($this->_dependency[$property])) {

            $this->_dependency[$property] = array(
                $dependProperty,
            );

        } else if (!in_array($dependProperty, $this->_dependency[$property])) {
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
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    final protected function _flushDependency($property)
    {
        if (isset($this->_dependency[$property])) {
            foreach ($this->_dependency[$property] as $dependProperty) {
                $this->unloadProperty($dependProperty);
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
    final public function validate($property, $value = null)
    {
        $camelCaseProperty = str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
        $methodName = 'get' . $camelCaseProperty . 'Validator';
        if (is_callable(array($this, $methodName))) {

            $validator = call_user_func(array($this, $methodName));

            // set dbitem for validator with setDbItem function
            if (is_callable(array($this, 'setDbItem'))) {
                $validator->setDbItem(&$this);
            }

            if (!$validator->isValid($value)) {
                throw new BaseZF_DbItem_Exception('Unable to validate property "' . $property . '" cause: ' . implode(', ', $validator->getMessages()));
            }
        }

        return $this;
    }

    //
    // Data loader and setter
    //

    /**
     * @todo remove link to db for more abstract item class
     */
    abstract function _loadData($ids);

    /**
     * Merge object data with new data
     *
     * @param array $data - record as assotiative array
     * @param boolean $isLoaded - is loaded from database or no
     *
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    protected function _setData($data, $isLoaded = false)
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

    /**
     * @todo remove link to db for more abstract item class
     */
    protected function _propertyNormalizedValueToItemFormat($property, &$data)
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
        if (strstr($type, '[]') == '[]' && !is_array($value)) {
            $data[$property] = self::str2arr($value);
        }

        // clean timestamp
        if (($type == 'TIMESTAMP' || $type == 'DATE') && !is_numeric($value)) {
            $data[$property] = strtotime($value);
        }

        return $this;
    }

    /**
     * @todo remove link to db for more abstract item class
     */
    protected function _propertyItemFormatToNormalizedValue($property, &$data)
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
        if (strstr($type, '[]') == '[]' && is_array($value)) {
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
        if (!$this->isModified()) {
            return $this;
        }

        if ($id = $this->_insert($this->_modified)) {

            $this->setId($id);
            $this->_setData($this->_modified);
            $this->_modified = array();
        }

        return $this;
    }

    /**
     * Insert new record to database
     * @todo remove link to db for more abstract item class
     *
     * @param array $propertyies assotiative array of properties
     *
     * @throw BaseZF_DbItem_Exception
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    abstract protected function _insert(array $properties);

    /**
     * Mass insert of new dbitems
     *
     * @param array an array of instance of dbitem
     *
     * @throw BaseZF_DbItem_Exception
     * @return true if success then false
     */
    public static function massInsert($items)
    {
        if (empty($items) || !is_array($items)) {
            return false;
        }

        reset($items);

        // @todo recover iterator position
        $current = current($items);

        try {

            foreach ($items as $item) {
                $item->insert();
            }

        } catch (Exception $e) {

            // reset ids
            foreach ($items as $item) {
                $item->setId(null);
            }

            throw new BaseZF_DbItem_Exception('Unable insert items to table "' . $current->_table . '" cause: ' . $e->getMessage());
        }

        return true;
    }

    //
    // Update
    //

    /**
     * Update modified property of dbitem
     */
    public function update()
    {
        // ignore not modified
        if (!$this->isModified()) {
            return $this;
        }

        if ($this->_update($this->_id, $this->_modified)) {
            $this->_setData($this->_modified);
            $this->_modified = array();
        }

        return $this;
    }

    /**
     * Update modified record to database
     * @todo remove link to db for more abstract item class
     *
     * @param array $propertyies assotiative array of properties
     *
     * @throw BaseZF_DbItem_Exception
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    abstract protected function _update($id, $properties);

    /**
     * Mass records update
     * @param array an array of instance of dbitem
     *
     * @throw BaseZF_DbItem_Exception
     * @return true if success then false
     */
    final public static function massUpdate($items)
    {
        if (empty($items) || !is_array($items)) return false;

        reset($items);

        // @todo recover iterator position
        $current = current($items);

        try {

            foreach ($items as $item) {
                $item->update(false);
            }

        } catch (Exception $e) {

            throw $e;
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
    final public static function massDelete(array $items)
    {
        $ids = array();
        foreach ($items as $item) {
            $ids[] = $item->getId();
        }

        reset($items);
        $current = current($items);

        // delete data
        $current->_delete($ids);

        // update dbitems
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
     * @todo remove link to db for more abstract item class
     *
     * @param integer $id unique key
     *
     * @throw BaseZF_DbItem_Exception
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    abstract protected function _delete($ids);

    //
    // Collections
    //

    /**
     * Retrieve DbItem on same collections
     *
     * @return array of BaseZF_DbItem instances
     */
    private function _getNeighboursItems()
    {
        $neighboursItems = array();

        // get neighbours from associated collection
        if (!empty($this->_collections)) {

            $neighboursItemsIds = array();
            foreach ($this->_collections as &$collection) {
                $neighboursItems = array_merge($neighboursItems, $collection->getItems());
            }

        // get neighbours from available instances
        } else {
             $neighboursItems = $this->_getInstances();
        }

        return $neighboursItems;
    }

    /**
     * Get item collection
     *
     * @return array of BaseZF_Collection instances
     */
    public function getCollection()
    {
        return $this->_collections;
    }

    /**
     * Set item collection
     *
     * @param object instance of BaseZF_DbCollection
     *
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    public function addCollection(BaseZF_DbCollection $collection)
    {
        $key = array_search($collection, $this->_collections);
        if ($key === false) {
           $this->_collections[] = &$collection;
        }

        return $this;
    }

    /**
     * Set item collection
     *
     * @param object instance of BaseZF_DbCollection
     *
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    public function removeCollection(BaseZF_DbCollection $collection)
    {
        $key = array_search($collection, $this->_collections);
        if ($key !== false) {
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
        return array_key_exists($property, $this->_modified) || array_key_exists($property, $this->_data);
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

    /**
     * Set a property, call the correct method to retrieve it and throw callback if isset
     *
     * @param string $property name of updated property
     * @param mixed $value value of updated property
     *
     * @throw BaseZF_DbItem_Exception
     * @return mixed Property value
     */
    public function __set($property, $value)
    {
        $this->setProperty($property, $value);

        return $this->getProperty($property);
    }

    //
    // Tools
    //

    /**
     * Reset all modified properties of Item
     *
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
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
     * @return object instance of BaseZF_DbItem alias current object instance for more fluent interface
     */
    public function unload()
    {
        // clean some things when object is reused
        $this->_data = array();
        $this->_modified = array();
        $this->_setLoaded(false);

        return $this;
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

        return $this->__toString() == $object->__toString();
    }

    /**
     * string builder called to display object has string
     */
    public function __toString()
    {
        return get_class($this) . '::' . spl_object_hash($this) . '(' . $this->getId() . ');';
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
            '_id'
        );
    }

    /**
     * Callback for unserialize object
     */
    public function __wakeup()
    {
        $id = $this->_id;
        $this->__construct(null);
        $this->setId($id);
    }


    //
    // Implement ArrayAccess
    //

    /**
     *
     */
    public function offsetSet($offset, $value) {
        return $this->$offset = $value;
    }

    /**
     *
     */
    public function offsetExists($offset) {
        return isset($this->$offset);
    }

    /**
     *
     */
    public function offsetUnset($offset) {
        $this->$offset = null;
    }

    /**
     *
     */
    public function offsetGet($offset) {
        return $this->$offset;
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
            self::destructExistInstance($this->_id);
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

