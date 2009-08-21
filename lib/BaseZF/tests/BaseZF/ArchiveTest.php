<?php
/**
 * ArchiveTest.php for BaseZF in tests/
 *
 * @category   Test
 * @package    Test_Example
 * @copyright  Copyright (c) 2008 Bahu
 * @author     Harold Thétiot (hthetiot)
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
        $fileData = '<html><h1>Toto ' . time() . '</h1></html>';
        $fileName = 'index-' . time() . '.html';

        // create archive
        $newArchive = BaseZF_Archive::newArchive('tar', $this->_archiveFilePath);
        $newArchive->addFileFromString($fileName, $fileData);
        $newArchive->createArchive();

        // extract archive && test archive format detection
        $readArchive =  BaseZF_Archive::extractArchive($this->_archiveFilePath);

        // check file after extract
        $archiveFiles = $readArchive->getFiles();
        $archiveTestFile = current($archiveFiles);

        $this->assertEquals($archiveTestFile['name'], $fileName);
        $this->assertEquals($archiveTestFile['data'], $fileData);
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

