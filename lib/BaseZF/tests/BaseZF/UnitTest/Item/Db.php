<?php
/**
 * BaseZF_Item_Db class in tests/BaseZF/Item
 *
 * @category  BaseZF
 * @package   BaseZF_UnitTest
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_UnitTest_Item_Db extends BaseZF_Item_Db_Abstract
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
     *
     * @return object Zend_Db instance
     */
    protected function _getDbInstance()
    {
        return BaseZF_UnitTest_Item_DbTest::$db;
    }

    /**
     * Retrieve the Zend_Cache instance
     *
     * @return object Zend_Cache instance
     */
    protected function _getCacheInstance()
    {
        return BaseZF_UnitTest_Item_DbTest::$cache;
    }

    /**
     * Retrieve the Zend_Log logger instance
     *
     * @return object Zend_log instance
     */
    protected function _getLogInstance()
    {
        return false;
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

