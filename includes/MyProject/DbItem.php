<?php
/**
 * DbItem class in /MyProject/
 *
 * @category   MyProject_Core
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class MyProject_DbItem extends BaseZF_DbItem
{
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
        return parent::getInstance($table, $id, $realtime, $class);
    }

    /**
     * Retrieve the Db instance
     */
    protected function _getDbInstance()
    {
        return MyProject::registry('db');
    }

    /**
     * Retrieve the Cache instance
     */
    protected function _getCacheInstance()
    {
        return MyProject::registry('dbcache');
    }

    /**
     * Retrieve the Logger instance
     */
    protected function _getLoggerInstance()
    {
        return MyProject::registry('logger');
    }

    /**
     * Retrieve the Database Schema as array
     */
    protected function &_getDbSchema()
    {
        return MyProject_DbSchema::$tables;
    }
}

