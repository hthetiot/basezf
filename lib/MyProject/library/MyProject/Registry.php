<?php
/**
 * MyProject_Registry class in /MyProject
 *
 * @category  MyProject
 * @package   MyProject_Core
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/MyProject/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 *
 * This class should registry features
 *
 * You can add some callback for MyProject_Registry->registry('YourRegistryEntryName');
 * for that you just have to add a function like following.
 * <code>
 * private static function _create<YourRegistryEntryName>()
 * {
 *      return new SingletonClass();
 * }
 * </code>
 *
 */

final class MyProject_Registry extends BaseZF_Framework_Registry
{
    /**
     * Return Existing instance
     *
     * @param string $class Late Static Bindings issue
     *
     * @return object ready to use instance of BaseZF_Bean child class
     */
    public static function getInstance($class = __CLASS__)
    {
        return parent::getInstance($class);
    }
}

