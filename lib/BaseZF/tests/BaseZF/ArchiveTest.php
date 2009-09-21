<?php
/**
 * ArchiveTest.php for BaseZF in tests/
 *
 * @category   BaseZF
 * @package    BaseZF_UnitTest
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

require_once dirname(__FILE__) . '/../TestHelper.php';

/**
 * Test class for BaseZF_Archive
 *
 * @group BaseZF
 */
class BaseZF_ArchiveTest extends PHPUnit_Framework_TestCase
{
    protected $_tmpFiles = array();

    protected $_tmpDirs = array();

    /**
     * Call before all test and on class test loading
     */
    public function setUp()
    {

    }

    private function _getTmpFile($prefix = null, $suffix = null)
    {
        $tmpDir = sys_get_temp_dir();
        $tmpFile = $tmpDir . '/' . $suffix . 'test-' . count($this->_tmpFiles) . $prefix;
        $tmpFileDir = dirname($tmpFile);

        if (!is_dir($tmpFileDir) && $tmpFileDir != $tmpDir) {
            $this->_tmpDirs[$tmpFileDir] = $tmpFileDir;
            mkdir($tmpFileDir);
        }

        $this->_tmpFiles[$tmpFile] = $tmpFile;

        return $tmpFile;
    }

    public function testAddSimpleFile()
    {
        // declare file data for testing
        $fileData = '<html><h1>Toto ' . time() . '</h1></html>';
        $fileLength = mb_strlen($fileData);
        $fileExt = 'html';
        $filePath = $this->_getTmpFile('.' . $fileExt);

        // add contennt on test file
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
        $this->assertEquals($archiveTestFile['name'], $filePath);       // test file name extraction
        $this->assertEquals($archiveTestFile['data'], $fileData);       // test file data extraction
        $this->assertEquals($archiveTestFile['ext'], $fileExt);         // test file ext
        $this->assertEquals($archiveTestFile['stat'][7], $fileLength);  // test file size
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
        $this->assertEquals($archiveTestFile['name'], $filePath);       // test file name extraction
        $this->assertEquals($archiveTestFile['data'], $fileData);       // test file data extraction
        $this->assertEquals($archiveTestFile['ext'], $fileExt);         // test file ext
        $this->assertEquals($archiveTestFile['stat'][7], $fileLength);  // test file size
    }

    public function testAddFilesFromDirPattern()
    {
        // create files data for testing
        $testFileContentTpl = '<html><h1>Toto %s</h1></html>';
        $testFileExt = 'html';
        $testFileExtExcluded = 'php';
        $testFiles  = array(
            $this->_getTmpFile('.' . $testFileExt, 'subdir1/') => array(),
            $this->_getTmpFile('.' . $testFileExt, 'subdir1/') => array(),
            $this->_getTmpFile('.' . $testFileExtExcluded, 'subdir1/') => array(),
        );

        // create test files list and properties for checking
        foreach ($testFiles as $testFile => &$testFileData) {

            $testFileContent = sprintf($testFileContentTpl, $testFile);
            file_put_contents($testFile, $testFileContent);

            $testFileData = array(
                'name'      => '/' . basename($testFile),
                'data'      => $testFileContent,
                'ext'       => pathinfo($testFile, PATHINFO_EXTENSION),
                'length'    => mb_strlen($testFileContent),
            );

            $dirPath = dirname($testFile);
        }

        // create archive
        $archiveFilePath = $this->_getTmpFile('.tar');
        $newArchive = BaseZF_Archive::newArchive('tar', $archiveFilePath);
        $newArchive->addFiles($dirPath . '/*.' . $testFileExt);
        $newArchive->createArchive();

        // extract archive && test archive format detection
        $readArchive =  BaseZF_Archive::extractArchive($archiveFilePath);

        // check file after extract
        $archiveFiles = $readArchive->getFiles();

        // checking integrety of data and check file did no have testFileExtExcluded value
        foreach ($archiveFiles as $archiveFile => $archiveFileData) {

            $testFile = $testFiles[$dirPath . $archiveFileData['name']];

            $this->assertEquals($archiveFileData['name'], $testFile['name']);       // test file name extraction
            $this->assertEquals($archiveFileData['data'], $testFile['data']);       // test file data extraction
            $this->assertEquals($archiveFileData['ext'], $testFile['ext']);         // test file ext
            $this->assertNotEquals($archiveFileData['ext'], $testFileExtExcluded);  // excluded file extentions
            $this->assertEquals($archiveFileData['stat'][7], $testFile['length']);  // test file size
        }
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

        foreach ($this->_tmpDirs as $tmpDir) {
            if (is_dir($tmpDir)) {
                rmdir($tmpDir);
            }
        }
    }
}

