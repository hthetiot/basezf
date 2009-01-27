<?php
/**
 * Example.php for Bahu in tests/
 *
 * @category   Test
 * @package    Test_Example
 * @copyright  Copyright (c) 2008 Bahu
 * @author     Harold Thétiot (hthetiot)
 */

class MyProject_Example
{
	/**
	 * Property value of Example class
	 */
	protected $_property = null;

	/**
	 * Update property value
	 *
	 * @param void $property new value of property
	 *
	 * @return $this for more fluent interface
	 */
	public function updateProperty($property)
	{
		$this->_property = $property;

		return $this;
	}

	/**
	 * Retreive property value
	 *
	 * @return void $this->_property value
	 */
	public function getProperty()
	{
		return $this->_property;
	}
}

