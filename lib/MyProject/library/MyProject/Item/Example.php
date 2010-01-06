<?php
/**
 * MyProject_Item_Db_Example class in /MyProject/Item/Db
 *
 * @category  MyProject
 * @package   MyProject_Item_Db
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/MyProject/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class MyProject_Item_Db_Example extends MyProject_Item_Db
{
   /**
    * const: default Table of Db Item
    */
    const TABLE = 'example';

    /**
     * Get instance of allready contructed object
     *
     * @param void $id unique object id
     * @param string $class item className
     *
     * @return BaseZF_DbItem object instance
     */
    public static function getInstance($table, $id = null, $realtime = false, $class = __CLASS__)
    {
        return parent::getInstance(self::TABLE, $id, $realtime, $class);
    }

    /*
        @todo
        - virtual property with dependency
        - property validator
        - plugins config
    */
}

