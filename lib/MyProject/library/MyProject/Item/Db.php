<?php
/**
 * DbItem class in /MyProject/
 *
 * @category   MyProject
 * @package    MyProject_Item_Db
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thetiot (hthetiot)
 */

class MyProject_Item_Db extends BaseZF_Item_Db_Abstract
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

    //
    // Cache and Db instance getter
    //

    /**
     * Retrieve the Zend_Db database conexion instance
     */
    protected function _getDbInstance()
    {
        return MyProject_Registry::getInstance()->registry('db');
    }

    /**
     * Retrieve the Zend_Cache instance
     */
    protected function _getCacheInstance()
    {
        return MyProject_Registry::getInstance()->registry('cache');
    }

    /**
     * Retrieve the Zend_Log logger instance
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

