<?php
/**
 * BaseZF_ArchiveTest class in tests/BaseZF
 *
 * @category  BaseZF
 * @package   BaseZF_UnitTest
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

require_once realpath(dirname(__FILE__) . '/../../') . '/TestHelper.php';

/**
 * Test class for BaseZF_Archive
 *
 * @group BaseZF
 * @group BaseZF_Archive
 */
class BaseZF_UnitTest_ArchiveTest extends PHPUnit_Framework_TestCase
{
    /**
     * Call before all test and on class test loading
     */
    public function setUp()
    {

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
    private function _getTmpFile($fileName, $filePath = null, $fileData = null)
    {
        BaseZF_UnitTest_TemporaryFile::setNameSpace(__CLASS__);

        $tmpFilePath = BaseZF_UnitTest_TemporaryFile::getFile($fileName, $filePath, $fileData);

        return $tmpFilePath;
    }

    public function testAddAndExtractSimpleFile()
    {
        // declare file data for testing
        $fileData = '<html><h1>Toto ' . time() . '</h1></html>';
        $fileLength = mb_strlen($fileData);
        $fileExt = 'html';
        $filePath = $this->_getTmpFile(time() . '.' . $fileExt);

        // add contennt on test file
        file_put_contents($filePath, $fileData);

        // create archive
        $archiveFilePath = $this->_getTmpFile(time() . '.tar');
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

    public function testAddAndExtractFileFromString()
    {
        // declare file data for testing
        $fileData = '<html><h1>Toto ' . time() . '</h1></html>';
        $fileLength = mb_strlen($fileData);
        $fileExt = 'html';
        $filePath = '/index-' . time() . '.' . $fileExt;

        // create archive
        $archiveFilePath = $this->_getTmpFile(time() . '.tar');
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

    public function testAddAndExtractFilesFromDirPattern()
    {
        // create files data for testing
        $testFileContentTpl = '<html><h1>Toto %s</h1></html>';
        $testFileExt = 'html';
        $testFileExtExcluded = 'php';
        $testFiles  = array(
            $this->_getTmpFile(time() . '-1.' . $testFileExt, '/subdir1') => array(),
            $this->_getTmpFile(time() . '-2.' . $testFileExt, '/subdir1') => array(),
            $this->_getTmpFile(time() . '-3.' . $testFileExtExcluded, '/subdir1') => array(),
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
        $archiveFilePath = $this->_getTmpFile(time() . '.tar');
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
        // remove tmp files
        BaseZF_UnitTest_TemporaryFile::clearFiles();
    }
}

