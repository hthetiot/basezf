<?php
/**
 * BaseZF_Collection_Abstract class in /BaseZF/Collection
 *
 * @category   BaseZF
 * @package    BaseZF_Item, BaseZF_Collection
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *             Oleg Stephanwhite (oleg)
 *             Fabien Guiraud (fguiraud)
 */

abstract class BaseZF_Collection_Abstract implements Iterator, Countable
{
    /**
     * Max recursive sub iteration for this Collection
     */
    const MAX_ITERATOR_DEPTH = 40;

    /**
     * Define the zend log priority
     */
    const LOG_PRIORITY = Zend_Log::DEBUG;

    /**
     * Array of unique Item Id identifiers
     */
    protected $_ids = array();

    /**
     * Saved positions current collection iterator
     */
    protected $_iteratorSavedPositions = array();

    //
    // Constructor
    //

    /**
     * Constructor
     *
     * @param array $ids array of unique id
     */
    public function __construct(array $ids = array())
    {
        $this->setIds($ids);

        $this->log('Create new Collection Instance : ' . $this);
    }

    /**
     * Destroy instance of collection and remove item dependency
     */
    public function __destruct()
    {
        // remove collection dependency on items
        foreach ($this as $id => $item) {
            $item->removeCollection($this);
        }
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
    // Some getter and setter
    //

    /**
     * Setter for Item ids of Collection
     *
     * @param array $ids an array of unique Item ids
     *
     * @return object current collection instance
     */
    final public function setIds(array $ids)
    {
        $ids = array_unique($ids);

        $this->_ids = array();

        foreach ($ids as $id) {
            $this->addId($id);
        }

        $this->rewind();

        return $this;
    }

    /**
     * Getter for Item ids of current Collection
     *
     * @return array $this->_ids value
     */
    final public function getIds()
    {
        return $this->_ids;
    }

    /**
     * Add an item to current Collection by id identifier
     *
     * @param string item id of item should be added to collection
     *
     * @return object current collection instance
     */
    final public function addId($id)
    {
        // @todo implement array param support

        if (mb_strlen($id) === 0) {
            throw new BaseZF_Collection_Db_Exception('Unable to add item by id to collection cause empty id identifer value provided.');
        }

        if (!in_array($id, $this->_ids)) {

            // create instance of item
            $item = $this->_getItemInstance($id);

            // add collection dependency on item
            $item->addCollection($this);

            $this->_ids[] = $id;
        }

        if (!$this->valid()) {
            $this->rewind();
        }

        return $this;
    }

    /**
     * Remove an item to current Collection by id identifier, not data deleted
     *
     * @param string item id of item should be removed from collection
     *
     * @return object current collection instance
     */
    final public function removeId($id)
    {
        // @todo implement array param support

        if (($key = array_search($id, $this->_ids)) !== false) {

            $item = $this->getItem($id);

            // remove collection dependency on item
            $item->removeCollection($this);

            unset($this->_ids[$key]);
        }

        return $this;
    }

    /**
     * Getter for random id of Item
     *
     * @param int number of expected results
     *
     * @return mixed array if count param greather than 1 else integer
     */
    final public function getRandomId($count = 1)
    {
        if (empty($this->_ids)) {
            return false;
        }

        $this->_saveIteratorPosition();

        try {

            $idx = array_rand($this->_ids, $count);

            if ($count > 1 && is_array($idx)) {

                $result = array();
                foreach ($idx as $i) {
                    $result[] = &$this->_ids[$i];
                }

            } else {
                $result = $this->_ids[$idx];
            }

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }

        return $result;
    }

    //
    // Item manager
    //

    /**
     * Get Item class name used as container stored by reference into this
     * collection
     *
     * @param string $className class name base name value
     *
     *
     */
    protected function _getItemClassName($className = null)
    {
        if (is_null($className)) {
            $className = get_class($this);
        }

        $itemClassName = str_replace('Collection', 'Item', $className);

        try {

            Zend_Loader::loadClass($itemClassName);

        } catch (Exception  $e) {

            $itemClassName = $this->_getItemClassName(__CLASS__);
        }

        return $itemClassName;
    }

    /**
     * Retreive item object by id if exist into collection
     *
     * @param string item id of item into collection
     *
     * @return object Item instance from current collection
     */
    final public function getItem($id)
    {
        if (!in_array($id, $this->_ids)) {
            throw new BaseZF_Collection_Db_Exception(sprintf('Item with id "%s" not found in this collection', $id));
        }

        return $this->_getItemInstance($id);
    }

    /**
     * Check if current collection have item has instance
     *
     * @return bool true if one item matche else false
     */
    final public function hasItem($itemMatch)
    {
        foreach ($this as $id => $item) {
            if ($item->isEqual($itemMatch)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get All items associate to current collection
     *
     * @return array an array of item indexed by item id
     */
    final public function getItems()
    {
        $result = array();
        $this->_saveIteratorPosition();

        try {

            foreach ($this as $id => $item) {
                $result[$id] = $item;
            }

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }

        return $result;
    }

    /**
     * Create a new item and associate it to collection
     *
     * @return object Item object instance of new created Item
     */
    public function newItem($data)
    {
        $newItem = $this->_getItemInstance();
        $newItem->setProperties($data);
        $newItem->addCollection(&$this);
        $newItem->insert();

        // add item to collection
        $this->addId($newItem->getId());

        return $newItem;
    }

    /**
     * Call Item ClassName getInstance
     *
     * @param string item id of item should be added to collection
     *
     * @return object instance of Item
     */
    protected function _getItemInstance($id = null)
    {
        $itemClassName = $this->_getItemClassName();

        return call_user_func(array($itemClassName, 'getInstance'), $id);
    }

    //
    // Item data
    //

    /**
     * Retrieve property items values has array
     *
     * @param string property item name
     *
     * @return array of property value
     */
    final public function getProperty($property)
    {
        $result = array();
        $this->_saveIteratorPosition();

        try {

            foreach ($this as $id => $item) {
                $result[$id] = $item->$property;
            }

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }

        return $result;
    }

    /**
     * Modify a propery: change value and mark as modified for all items
     *
     * @param string $property Property to modify
     * @param string $value New value
     * @param boolean $check Check identical values before update (default true)
     *
     * @return object current collection instance
     */
    final public function setProperty($property, $value)
    {
        $this->_saveIteratorPosition();

        try {

            foreach ($this as $id => $item) {
                $item->setProperty($property, $value);
            }

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }

        return $this;
    }

    /**
     * Modify properties: change values for multiple properties and mark as modified for all items
     *
     * @param array assotiative array of values
     *
     * @return object current collection instance
     */
    final public function setProperties($data)
    {
        $this->_saveIteratorPosition();

        try {

            foreach ($this as $item) {
                $item->setProperties($data);
            }

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }

        return $this;
    }

    /**
     * Call update on all item of collection
     *
     * @return object current collection instance
     */
    final public function update()
    {
        $this->_saveIteratorPosition();

        try {

            $itemClassName = $this->_getItemClassName();
            call_user_func(array($itemClassName, 'massUpdate'), $this->getItems());

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }

        return $this;
    }

    /**
     * Call delete on all item of collection
     *
     * @return object current collection instance
     */
    final public function delete()
    {
        $items = array();

        $this->_saveIteratorPosition();

        try {

            $itemClassName = $this->_getItemClassName();
            call_user_func(array($itemClassName, 'massDelete'), $this->getItems());

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }

        $this->setIds(array());

        return $this;
    }

    //
    // Implement Countable
    //

    /**
     * Get the number of item into the collection
     *
     * @return int number of item into the collection
     */
    final public function count()
    {
        return count($this->_ids);
    }

    //
    // Implement Iterator
    //

    final public function rewind()
    {
        reset($this->_ids);

        return $this;
    }

    final public function current()
    {
        $var = current($this->_ids);

        return ($var === false)? false : $this->getItem($var);
    }

    final public function key()
    {
        $var = current($this->_ids);

        return $var;
    }

    final public function next()
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
     * @return object current collection instance
     */
    final protected function _saveIteratorPosition($resetAfterSave = true)
    {
        if (count($this->_iteratorSavedPositions) > self::MAX_ITERATOR_DEPTH) {
            throw new BaseZF_Collection_Db_Exception(sprintf('Maximal iterator depth reached with limit set %d', self::MAX_ITERATOR_DEPTH));
        }

        $key = key($this->_ids);

        if ($key === null) {
            $key = 0;
        }

        array_push($this->_iteratorSavedPositions, $key);

        if ($resetAfterSave) {
            reset($this);
        }

        return $this;
    }

    /**
     * Load saved iterator position of $this->_items array
     *
     * @return object current collection instance
     */
    final protected function _loadIteratorPosition()
    {
        $pos = array_pop($this->_iteratorSavedPositions);

        if ($pos === null) {
            throw new BaseZF_Collection_Db_Exception('No saved position for iterator found');
        }

        reset($this->_ids);
        while (current($this->_ids) !== false) {

            if (key($this->_ids) == $pos) {
                break;
            }

            next($this->_ids);
        }

        current($this->_ids);

        return $this;
    }

    //
    // Serialize
    //

    /**
     * Callback fo serialize oject
     * @note: we serialize only usefull properties
     */
    public function __sleep()
    {
        return array
        (
            '_ids',
        );
    }

    /**
     * Callback for unserialize object
     */
    public function __wakeup()
    {
        $this->__construct($this->_ids);
    }

    //
    // Tools
    //

    /**
     * string builder called to display object has string
     */
    public function __toString()
    {
        return get_class($this) . '::' . spl_object_hash($this) . '(' . implode(',', $this->getIds()) . ');';
    }
}

