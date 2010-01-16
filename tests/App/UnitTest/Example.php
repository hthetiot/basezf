<?php
/**
 * App_Example class in /test/App
 *
 * @category  MyProject
 * @package   MyProject_App_UnitTest
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class App_UnitTest_Example
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

