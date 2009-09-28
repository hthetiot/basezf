<?php
/**
 * StCollection class in /BazeZF/
 *
 * @category   BazeZF
 * @package    BazeZF_StItem
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

abstract class BaseZF_StCollection extends ArrayObject
{
    /**
     * do not forget to specify into inherited class property:
     * protected $_data = array(...);
     */
    protected $_data = array();

    /**
     * Static instance cache
     */
    protected static $_STATIC_INSTANCES = array();

    /**
     * Constructor fill array object from array
     */
    public function __construct($itemClassName = null)
    {
        $rows = array();
        $has_numeric_key = false;
        $itemClassName = $this->_getItemClassName($itemClassName);

        foreach ($this->_data as $key => $item) {
            $row = new $itemClassName($key, $item);
            $rows[$key] = $row;
            $has_numeric_key = $has_numeric_key || strcmp(intval($key),$key)==0;
        }

        parent::__construct($rows);

        if (!$has_numeric_key) {
            $this->setFlags(ArrayObject::ARRAY_AS_PROPS);
        }
    }

    /**
     * Get instance of allready contructed object
     *
     * @return object instance of StCollection
     */
    static protected function getInstance($className, $itemClassName = null)
    {
        if (!isset(self::$_STATIC_INSTANCES[$className])) {
            self::$_STATIC_INSTANCES = &new $className($itemClassName);
        }

        return self::$_STATIC_INSTANCES;
    }

    //
    // StItem manager
    //

    /**
     * Get stItem class name
     *
     * @return string stItem classname
     */
    protected function _getItemClassName($collClassName = null)
    {
        if (is_null($collClassName)) {
            $collClassName = get_class($this);
        }

        $itemClassName = str_replace('_StCollection', '_StItem', $collClassName);

        try {

            Zend_Loader::loadClass($itemClassName);

            if (!class_exists($itemClassName, true)) {
                throw new Exception('not existing class '. $classItem);
            }

        } catch (Exception  $e) {

            $itemClassName = $this->_getItemClassName('BaseZF_StCollection');
        }

        return $itemClassName;
    }

    //
    // DbItem data
    //

    /**
     * Retrieve property items values has array
     *
     * @param string property item name
     *
     * @return array of property value index by stItem ids
     */
    public function getProperty($property)
    {
        $result = array();
        foreach ($this as $id=> $item) {
            $result[$id] = $item->$property;
        }

        return $result;
    }

    /**
     * Retrieve properties items values has array
     *
     * @param array properties item name
     *
     * @return array of properties value index by stItem ids
     */
    public function getProperties(array $properties)
    {
        $result = array();
        foreach ($this as $id=> $item) {
            foreach ($properties as $property) {
                $result[$id] = $item->$property;
            }
        }

        return $result;
    }
}

class BaseZF_StCollection_Exception extends BaseZF_Exception
{
}

