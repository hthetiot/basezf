<?php
/**
 * DbCollection class in /MyProject/
 *
 * @category   MyProject_Core
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

class MyProject_DbCollection extends BaseZF_DbCollection
{
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

