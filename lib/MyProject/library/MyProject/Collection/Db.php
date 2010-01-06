<?php
/**
 * MyProject_Collection_Db class in /MyProject/Collection/Db
 *
 * @category  MyProject
 * @package   MyProject_Item_Db
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/MyProject/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class MyProject_Collection_Db extends BaseZF_Collection_Db_Abstract
{
    /**
     * Retrieve the Zend_Db database conexion instance
     *
     * @return object Zend_Db instance
     */
    protected function _getDbInstance()
    {
        return MyProject_Registry::getInstance()->registry('db');
    }

    /**
     * Retrieve the Zend_Cache instance
     *
     * @return object Zend_Cache instance
     */
    protected function _getCacheInstance()
    {
        return MyProject_Registry::getInstance()->registry('cache');
    }

    /**
     * Retrieve the Zend_Log logger instance
     *
     * @return object Zend_log instance
     */
    protected function _getLogInstance()
    {
        return MyProject_Registry::getInstance()->registry('log');
    }

    /**
     * Retreive the schema, can be an array, Zend_config instance, Zend_Db instance or
     * a BaseZF_Item_Db_Schema_Abstract className
     *
     * @return mixed
     */
    protected function &_getDbChema()
    {
        static $inited = false;

        if($inited === false) {

             // require by BaseZF_Item_Db_Schema_Auto to get schema from database itseft
             BaseZF_Item_Db_Schema_Auto::loadSchemaFromDb(
                 $this->_getDbInstance(),
                 $this->_getCacheInstance()
             );

             $inited = true;
        }

        $dbSchemaClassName = 'BaseZF_Item_Db_Schema_Auto';

        return $dbSchemaClassName;
    }
}

