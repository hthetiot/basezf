<?php
/**
 * StCollection class in /BazeZF/
 *
 * @category   BazeZF_StCollection
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_StCollection extends ArrayObject
{
    protected $_disableExtendedId = false;

    /**
     * do not forget to specify into inherited class property:
     * protected $_data = array(...);
     */
    protected $_data = array();

	/**
     * Constructor fill array object from array
     */
    public function __construct($itemClassName = 'BaseZF_StItem')
    {
        $has_numeric_key = false;
        $rows = array();

        if(is_array($this->_data)) {

            foreach ($this->_data as $key => $item) {
                $row = new $itemClassName($key, $item);
                $rows[$key] = $row;
                $has_numeric_key = $has_numeric_key || strcmp(intval($key),$key)==0;
            }
        }

        parent::__construct($rows);

        if(!$has_numeric_key) {
            $this->setFlags(ArrayObject::ARRAY_AS_PROPS);
        }
    }

	/**
	 * Get instance of allready contructed object
	 *
	 * @return object instance of StCollection
	 */
	static protected function getInstance($className, $itemClassName = 'BaseZF_StItem')
	{
        static $instances;

        if(!isset($instances)) {
            $instances = array();
        }

        if(!isset($instances[$className])) {
            $instances[$className] = new $className($itemClassName);
        }

        return $instances[$className];
	}

    /**
     * Get a StItem from collection
     *
     * @param void $id stiem id
     *
     * @return BaseZF_StItem object
     */
    protected function _getStItem($id)
    {
        if (!$this->_disableExtendedId) {
            $id = BaseZF_StItem::getIdFromExtendedId($id);
        }

        if (!parent::offsetExists($id)) {
            throw new BaseZF_StCollection_Exception('No existing StItem for id : "' . $id . '"');
        }

        return parent::offsetGet($id);
    }

    /**
     * @todo doc
     */
    public function getProperty($property = 'name')
    {
        $value = array();
        foreach ($this as $id => $data) {
            $value[$id] = $data[$property];
        }

        return $value;
    }
}

class BaseZF_StCollection_Exception extends BaseZF_Exception {}

