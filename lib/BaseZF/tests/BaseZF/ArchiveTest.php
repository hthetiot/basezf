<?php
/**
 * ArchiveTest.php for BaseZF in tests/
 *
 * @category   BaseZF
 * @package    BaseZF_UnitTest
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

// Load PhpUnit Libs
require_once 'PHPUnit/Framework.php';

// Load BaseZF Libs
require_once 'BaseZF/Archive.php';

class BaseZF_ArchiveTest extends PHPUnit_Framework_TestCase
{
    protected $_archiveFilePath;

    /**
     * Call before all test and on class test loading
     */
    public function setUp()
    {
        // configure test here

        $this->_archiveFilePath = sys_get_temp_dir() . '/test.tar';
    }

    public function testAddFileFromString()
    {
        // declare file data for testing
        $fileData = '<html><h1>Toto ' . time() . '</h1></html>';
        $fileLength = mb_strlen($fileData);
        $fileExt = 'html';
        $fileName = 'index-' . time() . '.' . $fileExt;

        // create archive
        $newArchive = BaseZF_Archive::newArchive('tar', $this->_archiveFilePath);
        $newArchive->addFileFromString($fileName, $fileData);
        $newArchive->createArchive();

        // extract archive && test archive format detection
        $readArchive =  BaseZF_Archive::extractArchive($this->_archiveFilePath);

        // check file after extract
        $archiveFiles = $readArchive->getFiles();
        $archiveTestFile = current($archiveFiles);

        // checking integrety of data
        $this->assertEquals($archiveTestFile['name'], $fileName); // test file name extraction
        $this->assertEquals($archiveTestFile['data'], $fileData); // test file data extraction
        $this->assertEquals($archiveTestFile['ext'], $fileExt); // test file ext
        $this->assertEquals($archiveTestFile['stat'][7], $fileLength); // test file size
    }

    /**
     * Call after all test and on class test loading
     */
    public function tearDown()
    {
        // clean database or test generated data for example
        if (is_file($this->_archiveFilePath)) {
            unlink($this->_archiveFilePath);
        }
    }
}

