<?php
/**
 * DbCollection class in /MyProject/
 *
 * @category   MyProject
 * @package    MyProject_DbItem
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

class MyProject_DbCollection extends BaseZF_DbCollection
{
    /**
     * Retrieve the Db instance
     */
    protected function _getDbInstance()
    {
        return MyProject_Registry::getInstance()->registry('db');
    }

    /**
     * Retrieve the Cache instance
     */
    protected function _getCacheInstance()
    {
        return MyProject_Registry::getInstance()->registry('cache');
    }

    /**
     * Retrieve the Logger instance
     */
    protected function _getLoggerInstance()
    {
        return MyProject_Registry::getInstance()->registry('log');
    }

    /**
     * Retrieve the Database Schema as array
     */
    protected function &_getDbSchema()
    {
        return MyProject_DbSchema::$tables;
    }
}

