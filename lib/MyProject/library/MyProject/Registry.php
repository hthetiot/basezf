<?php
/**
 * Registry.php
 *
 * @category   MyProject
 * @package    MyProject_Core
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
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
