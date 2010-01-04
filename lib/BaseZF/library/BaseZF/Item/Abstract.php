<?php
/**
 * BaseZF_Item_Abstract class in /BaseZF/Item
 *
 * @category   BaseZF
 * @package    BaseZF_Item, BaseZF_Collection
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *             Oleg Stephanwhite (oleg)
 *             Fabien Guiraud (fguiraud)
 */


/*
 Storage: array, db, db+cache : loadData/_insert/_delete/_update
 Plugin: netstedset, toJson, toXML, toHTMLTable, to array
 Event : insert/update/setProperty/getProperty

 // db
 BaseZF_Item_Registry::getInstance($namespace, $id);
 BaseZF_Item_Abstract($id);
 BaseZF_Item_Exception();

 BaseZF_Collection_Abstract($ids);
 BaseZF_Collection_Exception();

 // db
 BaseZF_Item_Db_Abstract($id, $table, $realtime);
 BaseZF_Item_Db_Exception();

 BaseZF_Item_Db_Query();
 BaseZF_Item_Db_Query_Exception();

 BaseZF_Item_Db_Schema_Abstract();
 BaseZF_Item_Db_Schema_Exception();

 BaseZF_Collection_Db_Abstract($ids, $table $realtime);
 BaseZF_Collection_Db_Exception();

 // array (old static)
 BaseZF_Item_Array($id, $namespace);
 BaseZF_Item_Array_Exception();
 BaseZF_Item_Array_Data();

 BaseZF_Collection_Array($ids, $namespace);
 BaseZF_Collection_Array_Exception();



 // helper
 BaseZF_Item_Helper_Abstract
 BaseZF_Item_Helper_Array           // Array->toArray()
 BaseZF_Item_Helper_Json            // Json->toJson, Json->setPropertiesFromJson
 BaseZF_Item_Helper_XML             // XML->toXML, XML->setPropertiesFromXML

 BaseZF_Collection_Helper_Abstract
 BaseZF_Collection_Helper_Array     // toArray
 BaseZF_Collection_Helper_Json      // toJson, setPropertiesFromJson
 BaseZF_Collection_Helper_XML       // toXML, setPropertiesFromXML

 // Observer
 BaseZF_Item_Observer_Abstract
 BaseZF_Item_Observer_NestedSet     //


*/

abstract class BaseZF_Item_Abstract implements ArrayAccess
{
    /**
     * Extended item id slat phrase
     */
    const EXTENTED_ID_SALT = '14041985';

    /**
     * Extended item id phrase preffix
     */
    const EXTENTED_ID_PREFFIX = 'x';

    /**
     * Max item loaded per load
     */
    const MAX_ITEM_BY_LOAD = 100;

    /**
     * Define the zend log priority
     */
    const LOG_PRIORITY = Zend_Log::DEBUG;


    const NOT_LOADED_PROPERTY_VALUE = null;

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
    public function __construct($id = null)
    {
        $this->setId($id);

        $this->log('Create Item Instance: ' . $this);
    }

    /**
     * Destroy instance of object
     */
    public function __destruct()
    {
        // clean collection dependency
        foreach ($this->_collections as $collection) {
            $this->removeCollection($collection);
        }

        // remove instance from registry if not deleted (cause delete remove it)
        if(BaseZF_Item_Registry::removeItemInstance($this, get_class($this)) === false) {
            die(sprintf('Unable to destuct item "%s" properly', $this));
        }

        echo 'kill:' . $this;
    }

    //
    // Log Message
    //

    /**
     * Log events append into internal collection engine
     *
     * @param string $msg log message
     *
     * @return object current collection instance
     */
    protected function log($msg)
    {
        if ($logger = $this->_getLogInstance()) {
            $logger->log($msg, self::LOG_PRIORITY);
        }

        return $this;
    }

    //
    // Singleton Item instance manager
    //

    /**
     * Get Instance from BaseZF_Item_Registry
     *
     * @param mixed $id unique item identifier
     * @param string $className item classname
     *
     * @return object instance of $className
     */
    protected static function &_getInstance($id = null, $className = __CLASS__)
    {
        $item = BaseZF_Item_Registry::getItemInstanceById($id, $className);

        if (!$item instanceof BaseZF_Item_Abstract) {
            throw new BaseZF_Item_Exception(sprintf('Unable to get instance of object "%s" with id "%s" from BaseZF_Item_Registry.', $className, $id));
        }

        return $item;
    }

    //
    // Some getter and setter
    //

    /**
     * Set unique id
     *
     * @param void $id unique item identifier
     *
     * @return object current Item instance
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

        // update instance in registry
        BaseZF_Item_Registry::saveItemInstance($this, get_class($this));

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
            $id = base_convert($matches[1], 36, 10) - self::EXTENTED_ID_SALT;
        } else {
            return null;
        }

        return $id;
    }

    /**
     * Generate extended id from original unique id
     *
     * @return string extended id generated by conversion of id to base 36 with a salt
     */
    final static public function getItemExtendedId($id)
    {
        return self::EXTENTED_ID_PREFFIX . base_convert($id + self::EXTENTED_ID_SALT, 10, 36);
    }

    /**
     * Generate extended id from original unique id
     *
     * @return string extended id generated by conversion of id to base 36 with a salt
     */
    final public function getExtendedId()
    {
        return self::getItemExtendedId($this->getId());
    }

    //
    // State setter and getter
    //

    /**
     * Check if item is deleted
     *
     * @return bool true if deleted else false
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
     * Check if item is loaded
     *
     * @return bool true if loaded else false
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
     * Check if any property is modified
     *
     * @return bool true if enable
     */
    final public function isModified()
    {
        return !empty($this->_modified);
    }

    /**
     * Check if item has an id
     *
     * @return bool true if loaded else false
     */
    final public function hasId()
    {
        return !is_null($this->_id);
    }

    //
    // Properties Func
    //

    /**
     * Get property or virtual property value of current item
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
     * @return new property value
     */
    public function setProperty($property, $value)
    {

        if (!$this->isProperty($property)) {
            throw new BaseZF_Item_Exception(sprintf('Unable to set value to property "%s->%s" cause: property is not found in data, may it is a virtual property.', $this, $property));
        }

        // check same property
        if ($this->_checkValueSame && $this->isPropertyLoaded($property) && $value === $this->_data[$property]) {
            return $this;
        }

        // @todo convert property value to standart format
        // empty string value is null value by default
        if (mb_strlen(trim($value)) == 0) {
            $value = null;
        }

        $this->validatePropertyValue($property, $value);
        $this->_modified[$property] = $value;
        $this->_flushDependency($property);

        return $this->getProperty($property);
    }

    /**
     * Set value of a non virtual properties
     *
     * @param array $data array an array assoc of property value indexed by property name
     *
     * @return new non virtual property value
     */
    public function setProperties(array $data)
    {
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
     * @return new virtual property value
     */
    public function setVirtualProperty($property, $value, $propertyDependency = null)
    {
        if ($this->getPropertyType($property)) {
            throw new BaseZF_Item_Exception(sprintf('Unable to set value to virtual property "%s->%s" cause: property found in structure.', $this, $property));
        }

        $this->$property = $value;

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
     * @return object current Item instance
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
     * Get Ids will allso load
     *
     * @param string $property
     * @param array $prefereIds
     * @param int $limit
     *
     * @return array an array of item id
     */
    protected function _getIdsNeedToLoad($property, $prefereIds = array(), $limit = self::MAX_ITEM_BY_LOAD)
    {
        // get neighbours ids
        $neighboursIds = array();
        $neighboursItems = $this->_getNeighboursItems();

        foreach ($neighboursItems as $neighboursItem) {

            $neighboursItemId = $neighboursItem->getId();
            if (
                array_search($neighboursItemId, $prefereIds) === false &&
                !$neighboursItem->isPropertyLoaded($property)
            ) {

                $neighboursIds[] = $neighboursItemId;
                $limit--;

                if ($limit <= 0) {
                    break;
                }
            }
        }

        return array_merge($neighboursIds, $prefereIds);
    }

    /**
     * Load property of Items
     *
     * @param array $ids item ids of item should be loaded
     * @param string $property name of property
     *
     * @return object current Item instance
     */
    final protected function _massLoadProperty(array $ids, $property)
    {
        if (empty($ids)) {
            return $this;
        }

        try {

            // load from db or cache
            $data = $this->_loadData($ids);
            foreach ($data as $id => $row) {
                if ($item = self::_getInstance($id, get_class($this))) {
                    $item->_setData($row);
                    $item->_setLoaded(true);
                }
            }

        } catch (BaseZF_Item_Exception $e) {
            throw new BaseZF_Item_Exception(sprintf('Unable load item property "%s" from items cause: %s', $property, $e->getMessage()));
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
    final public function isProperty($property)
    {
        return array_key_exists($property, $this->_data);
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
        $notLoadedValue = self::getNotLoadedPropertyDefaultValue();

        return $this->isProperty($property) && $this->_data[$property] !== $notLoadedValue ? true : false;
    }

    /**
     * Unload property from database or modified
     *
     * @param string $property name of property
     */
    final public function unloadProperty($property)
    {
        // clear modified if exist and
        if ($this->isProperty($property)) {
            $this->resetProperty($property);
            $this->_data[$property] = self::getNotLoadedPropertyDefaultValue();
            $this->_setLoaded(false);
        }

        return $this;
    }

    final protected function _initProperties($propertiesNames)
    {
        $this->_setData(array_fill_keys($propertiesNames, null));
        $this->_setLoaded(false);

    }

    final protected function getNotLoadedPropertyDefaultValue()
    {
        return self::NOT_LOADED_PROPERTY_VALUE;
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
     * @return object current Item instance
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
     * @return object current Item instance
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
    // Validators callback methods for peroperty
    //

    /**
     * Try to call validator method for a property
     *
     * @param string $property name of property to validate
     */
    final public function validatePropertyValue($property, $value = null, $throwException = true)
    {
        $camelCaseProperty = str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
        $methodName = 'get' . $camelCaseProperty . 'Validator';

        if (is_callable(array($this, $methodName))) {

            $validator = call_user_func(array($this, $methodName));

            if (is_null($value)) {
                $value = $this->getProperty($property);
            }

            if (!$validator->isValid($value) && $throwException) {
                throw new BaseZF_Item_Exception(sprintf('Unable to validate property "%s" cause: %s',  $property, implode(', ', $validator->getMessages())));
            }

            return $validator;
        }
    }

    //
    // Properties values (alias Data) setter and loader
    //

    /**
     * Extract data from storage indexed by Item id
     *
     * @param array $ids item ids of item should be loaded
     *
     * @return array an array of properties data from storage indexed by Item ids
     */
    abstract protected function _loadData($ids);

    /**
     * Merge object data with new data
     *
     * @param array $data record as assotiative array
     * @param boolean $isLoaded is loaded from database or no
     *
     * @return object current Item instance
     */
    protected function _setData(array $data)
    {
        // @todo convert property value to standart format

        $this->_data = array_merge($this->_data, $data);

        return $this;
    }

    //
    // Insert
    //

    /**
     * Insert new record
     */
    final public function insert()
    {
        // ignore not modified
        if (!$this->isModified()) {
            return $this;
        }

        if ($id = $this->_insert($this->_modified)) {

            $this->setId($id);
            $this->_setData($this->_modified);
            $this->_setLoaded(true);

            // clear modified
            $this->_modified = array();
        }

        return $this;
    }

    /**
     * Insert new record to storage
     *
     * @param array $propertyies assotiative array of properties
     *
     * @throw BaseZF_Item_Exception
     * @return object current Item instance
     */
    abstract protected function _insert(array $properties);

    /**
     * Mass insert of new Items
     *
     * @param array an array of instance of Item
     *
     * @throw BaseZF_Item_Exception
     * @return true if success then false
     */
    final public static function massInsert($items)
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

            throw new BaseZF_Item_Exception('Unable insert items cause: ' . $e->getMessage());
        }

        return true;
    }

    //
    // Update
    //

    /**
     * Update modified property of Item
     */
    final public function update()
    {
        // ignore not modified
        if (!$this->isModified()) {
            return $this;
        }

        if ($this->_update($this->_id, $this->_modified)) {
            $this->_setData($this->_modified);
            $this->_setLoaded(true);
            $this->_modified = array();
        }

        return $this;
    }

    /**
     * Update modified record to storage
     *
     * @param array $id item id to update
     * @param array $properties assotiative array of properties
     *
     * @throw BaseZF_Item_Exception
     * @return object current Item instance
     */
    abstract protected function _update($id, $properties);

    /**
     * Mass records update
     * @param array an array of instance of Item
     *
     * @throw BaseZF_Item_Exception
     * @return true if success then false
     */
    final public static function massUpdate(array $items)
    {
        if (empty($items) || !is_array($items)) return false;

        foreach ($items as $item) {
            $item->update();
        }

        return true;
    }

    //
    // Delete
    //

    /**
     * Delete record
     */
    final public function delete()
    {
        $id = $this->_id;
        if (empty($id)) return $this;

        $this->_delete($id);

        foreach ($this->_collections as $collection) {
            $collection->removeId($id);
        }

        $this->_setDeleted();

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

        // delete data using first item instance
        reset($items);
        $current = current($items);
        $current->_delete($ids);

        // update Items
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
     * Delete record from storage
     *
     * @param array $ids unique items ids should me deleted
     *
     * @throw BaseZF_Item_Exception
     * @return object current Item instance
     */
    abstract protected function _delete($ids);

    //
    // Collections
    //

    /**
     * Retrieve Item on same collections
     *
     * @return array of BaseZF_Item instances
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
        }

        return $neighboursItems;
    }

    /**
     * Get item collection
     *
     * @return array of BaseZF_Collection instances
     */
    final public function getCollection()
    {
        return $this->_collections;
    }

    /**
     * Set item collection
     *
     * @param object instance of BaseZF_Collection_Abstract
     *
     * @return object current Item instance
     */
    final public function addCollection(BaseZF_Collection_Abstract $collection)
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
     * @param object instance of BaseZF_Collection_Abstract
     *
     * @return object current Item instance
     */
    final public function removeCollection(BaseZF_Collection_Abstract $collection)
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
    final public function __isset($property)
    {
        return array_key_exists($property, $this->_modified) || array_key_exists($property, $this->_data);
    }

    /**
     * Get a property, call the correct method to retrieve it and throw callback if isset
     *
     * @param string $property name of requested property
     *
     * @throw BaseZF_Item_Exception
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
     * @throw BaseZF_Item_Exception
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
     * @return object current Item instance
     */
    final public function reset()
    {
        // clean some things when object is reused
        $this->_modified = array();

        return $this;
    }

    /**
     * unload loaded properties of Item
     *
     * @return object current Item instance
     */
    final public function unload()
    {
        // clean some things when object is reused
        $this->_data = array();
        $this->_modified = array();
        $this->_setLoaded(false);

        return $this;
    }

    /**
     * Compare current item instance with another
     *
     * @param object $object dbobject compare with current
     *
     * @return true if is the same object, else false
     */
    final public function isEqual($compareItem)
    {
        if (get_class($compareItem) != get_class($this)) {
            return false;
        }

        return spl_object_hash($this) === spl_object_hash($compareItem);
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
    final public function offsetSet($offset, $value) {
        return $this->$offset = $value;
    }

    /**
     *
     */
    final public function offsetExists($offset) {
        return isset($this->$offset);
    }

    /**
     *
     */
    final public function offsetUnset($offset) {
        $this->$offset = null;
    }

    /**
     *
     */
    final public function offsetGet($offset) {
        return $this->$offset;
    }
}

