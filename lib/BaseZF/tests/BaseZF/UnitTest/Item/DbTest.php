<?php
/**
 * BaseZF_ItemTest class in tests/BaseZF
 *
 * @category  BaseZF
 * @package   BaseZF_UnitTest
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

require_once realpath(dirname(__FILE__) . '/../../../') . '/TestHelper.php';

/**
 * Test class for Item
 *
 * @group BaseZF
 * @group BaseZF_Item
 * @group BaseZF_Item_Db
 */
class BaseZF_UnitTest_Item_DbTest extends PHPUnit_Framework_TestCase
{
    public static $db;

    public static $cache;

    /**
     * Call before all test and on class test loading
     */
    public function setUp()
    {
        self::_initUnitTestDb();
        self::_initUnitTestCache();
    }

    /**
     * Create SqlLite database for UnitTesting and init self::$db
     * has Zend_Db_Adapter ready to use instance
     *
     * @return void
     */
    protected static function _initUnitTestDb()
    {
        // init once only
        if (isset(self::$db)) {
            return;
        }

        // create a new SqlLite database file
        $databaseFilePath = self::_getTmpFile('example_database.db');

        // deplare schema
        $query = "
            CREATE TABLE example (
                example_id int,
                string text,
                PRIMARY KEY (example_id)
            );
        ";

        // create schema
        $dbSchema = new PDO('sqlite:' . $databaseFilePath);
        $dbSchema->query($query);

        // create connexion
        self::$db = Zend_Db::factory('Pdo_Sqlite', array(
            'dbname' => $databaseFilePath,
        ));
    }

    /**
     * Create Cache database for UnitTesting and init self::$cache
     * has Zend_Cache ready to use instance
     *
     * @return void
     */
    protected static function _initUnitTestCache()
    {
        // init once only
        if (isset(self::$cache)) {
            return;
        }

        // set cache options
        $frontendOptions = array(
            'lifetime' => 7200,
            'automatic_serialization' => true,
        );

        $backendOptions = array(
            'cache_dir' => BaseZF_UnitTest_TemporaryFile::getTmpPath(),
        );

        // create cache
        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

    /**
     * Create a Tmp file and add it into file should remove at the end
     *
     * @param string $fileName file name
     * @param string $filePath path of file
     * @param string $fileData content of file
     *
     * @return string new tmp file path
     */
    private static function _getTmpFile($fileName, $filePath = null, $fileData = null)
    {
        BaseZF_UnitTest_TemporaryFile::setNameSpace(__CLASS__);

        $tmpFilePath = BaseZF_UnitTest_TemporaryFile::getFile($fileName, $filePath, $fileData);

        return $tmpFilePath;
    }

    public function testCreateNewItem()
    {
        /*
        $newItem = BaseZF_UnitTest_Item_Db::getInstance('example');
        $newItem->string = 'toto';
        $newItem->insert();

        $item = BaseZF_UnitTest_Item_Db::getInstance('example', $newItem->getId(), true);
        echo $item->string;
        */
    }

    public function testUpdateItem()
    {
    }

    public function testGetItemExisting()
    {
    }

    public function testDeleteItemTest()
    {
    }

    public function testGetItemNonExisting()
    {
    }

    /**
     * Call after all test and on class test loading
     */
    public function tearDown()
    {
        // remove tmp files
        BaseZF_UnitTest_TemporaryFile::clearFiles();
    }
}

