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
    protected $_tmpFiles = array();

    /**
     * Call before all test and on class test loading
     */
    public function setUp()
    {

    }

    private function _getTmpFile($prefix = null, $suffix = null)
    {
        $tmpFile = sys_get_temp_dir() . '/' . $suffix . 'test-' . count($this->_tmpFiles) . $prefix;

        $this->_tmpFiles[] = $tmpFile;

        return $tmpFile;
    }

    public function testAddFilesDir()
    {
        // declare file data for testing
        $fileData = '<html><h1>Toto ' . time() . '</h1></html>';
        $fileLength = mb_strlen($fileData);
        $fileExt = 'html';
        $filePath = $this->_getTmpFile('.' . $fileExt, 'subdir1/');
        $dirPath = dirname($filePath);

        // add contenn on test file
        file_put_contents($filePath, $fileData);

        // create archive
        $archiveFilePath = $this->_getTmpFile('.tar');
        $newArchive = BaseZF_Archive::newArchive('tar', $archiveFilePath);
        $newArchive->addFiles($dirPath . '/*');
        $newArchive->createArchive();

        // extract archive && test archive format detection
        $readArchive =  BaseZF_Archive::extractArchive($archiveFilePath);

        // check file after extract
        $archiveFiles = $readArchive->getFiles();
        $archiveTestFile = current($archiveFiles);

        // checking integrety of data
        $this->assertEquals($archiveTestFile['name'], $filePath); // test file name extraction
        $this->assertEquals($archiveTestFile['data'], $fileData); // test file data extraction
        $this->assertEquals($archiveTestFile['ext'], $fileExt); // test file ext
        $this->assertEquals($archiveTestFile['stat'][7], $fileLength); // test file size
    }

    public function testAddFile()
    {
        // declare file data for testing
        $fileData = '<html><h1>Toto ' . time() . '</h1></html>';
        $fileLength = mb_strlen($fileData);
        $fileExt = 'html';
        $filePath = $this->_getTmpFile('.' . $fileExt);

        // add contenn on test file
        file_put_contents($filePath, $fileData);

        // create archive
        $archiveFilePath = $this->_getTmpFile('.tar');
        $newArchive = BaseZF_Archive::newArchive('tar', $archiveFilePath);
        $newArchive->addFile($filePath);
        $newArchive->createArchive();

        // extract archive && test archive format detection
        $readArchive =  BaseZF_Archive::extractArchive($archiveFilePath);

        // check file after extract
        $archiveFiles = $readArchive->getFiles();
        $archiveTestFile = current($archiveFiles);

        // checking integrety of data
        $this->assertEquals($archiveTestFile['name'], $filePath); // test file name extraction
        $this->assertEquals($archiveTestFile['data'], $fileData); // test file data extraction
        $this->assertEquals($archiveTestFile['ext'], $fileExt); // test file ext
        $this->assertEquals($archiveTestFile['stat'][7], $fileLength); // test file size
    }

    public function testAddFileFromString()
    {
        // declare file data for testing
        $fileData = '<html><h1>Toto ' . time() . '</h1></html>';
        $fileLength = mb_strlen($fileData);
        $fileExt = 'html';
        $filePath = '/index-' . time() . '.' . $fileExt;

        // create archive
        $archiveFilePath = $this->_getTmpFile('.tar');
        $newArchive = BaseZF_Archive::newArchive('tar', $archiveFilePath);
        $newArchive->addFileFromString($filePath, $fileData);
        $newArchive->createArchive();

        // extract archive && test archive format detection
        $readArchive =  BaseZF_Archive::extractArchive($archiveFilePath);

        // check file after extract
        $archiveFiles = $readArchive->getFiles();
        $archiveTestFile = current($archiveFiles);

        // checking integrety of data
        $this->assertEquals($archiveTestFile['name'], $filePath); // test file name extraction
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
        foreach ($this->_tmpFiles as $tmpFile) {
            if (is_file($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}

