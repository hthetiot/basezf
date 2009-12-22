<?php
/**
 * DbCollection class in /BaseZF
 *
 * @category   BaseZF
 * @package    BaseZF_Item, BaseZF_Collection
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 *             Oleg Stephanwhite (oleg)
 *             Fabien Guiraud (fguiraud)
 */

abstract class BaseZF_Collection implements Iterator, Countable
{
    /**
     * Max Item recursive sub iteration for this Collection
     */
    const MAX_ITERATOR_DEPTH = 40;

    /**
     * Array of unique Item Id identifiers
     */
    protected $_ids = array();

    /**
     * Saved positions current collection iterator
     */
    protected $_iteratorSavedPosition = array();

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

        // init default filter
        $this->filterReset();

        $this->log('Create new Collection Instance : ' . $this);
    }

    /**
     * Destroy instance of object
     */
    public function __destruct()
    {

    }

    //
    // Log instance getter
    //

    /**
     * Log events append into internal collection engine
     *
     * @param string $msg log message
     *
     * @return object current collection instance
     */
    protected function log($msg);

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
        $itemClassName = $this->_getItemClassName();

        foreach ($ids as $id) {
            $ids[] = &$itemClassName::getInstance($id);
        }

        $this->_ids = $ids;

        // create DbItem object for each id if not exist
        // DISABLE for performances isssue
        //foreach ($this as $item);

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
            throw new Exception('Unable to add item by id to collection cause empty id identifer value provided.');
        }

        if (!in_array($id, $this->_ids)) {
            $this->_ids[] = $id;
            $this->getItem($id);
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

            $item = $this->getItem($id)
                         ->removeCollection($this);

            unset($this->_ids[$key]);
        }

        return $this;
    }

    /**
     * Getter for random id of DbItem
     *
     * @param int number of expected results
     *
     * @return mixed array if count param grether than 1 then integer
     */
    public function getRandomId($count = 1)
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
    // DbItem manager
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

        } catch (BaseZF_Error_Exception  $e) {

            $itemClassName = $this->_getItemClassName(__CLASS__);
        }

        return $itemClassName;
    }

    /**
     * Retreive item object by id if exist into collection
     *
     * @return object Item instance from current collection
     */
    public function getItem($id)
    {
        if (!in_array($id, $this->_ids)) {
            throw new BaseZF_DbCollection_Exception('item with id "' . $id . '" not found in this collection');
        }

        $itemClassName = $this->_getItemClassName();
        $item = call_user_func(array($itemClassName, 'getInstance'), $id);

        // add collection to dependency
        $item->addCollection($this);

        return $item;
    }


    public function getItems()
    {
        $result = array();
        $this->_saveIteratorPosition();

        try {

            foreach ($this as $id => $item) {
                $result[$id] = &$item;
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
        $itemClassName = $this->_getItemClassName();
        $newItem = call_user_func(array($itemClassName, 'getInstance'), null);

        $newItem->setProperties($data);
        $newItem->addCollection(&$this);
        $newItem->insert();

        // add item to collection
        $this->addId($newItem->getId());

        // clear cache cause we add an item
        $this->clearCache();

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
     * @return boolean modified state
     */
    public function setProperty($property, $value)
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
     * @return boolean modified state
     */
    public function setProperties($data)
    {
        $this->_saveIteratorPosition();

        try {

            foreach ($this as $id => $item) {
                $item->setProperties($data);
            }

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }

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

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }


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

            $this->_loadIteratorPosition();

        } catch (Exception $e) {

            $this->_loadIteratorPosition();

            throw $e;
        }

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
        if (count($this->_iteratorSavedPosition) > self::MAX_ITERATOR_DEPTH) {
            throw new BaseZF_DbCollection_Exception(sprintf('Maximal iterator depth reached with limit set %d', self::MAX_ITERATOR_DEPTH));
        }

        $key = key($this->_ids);

        if ($key === null) {
            $key = 0;
        }

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

        if ($pos === null) {
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

