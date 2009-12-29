<?php
/**
 * MyProject_Collection_Db class in /MyProject/Collection/Db
 *
 * @category   MyProject
 * @package    MyProject_Collection_Db
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */


class MyProject_Collection_Db extends BaseZF_Collection_Db_Abstract
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
    protected function _getLogInstance()
    {
        return MyProject_Registry::getInstance()->registry('log');
    }

    /**
     * Retrieve the Database Schema as array
     */
    protected function _getTableStructure()
    {
        BaseZF_Item_Db_Schema_Auto::loadSchemaFromDb($this->_getDbInstance());

        return BaseZF_Item_Db_Schema_Auto::getTableStructure($this->getTable());
    }
}

