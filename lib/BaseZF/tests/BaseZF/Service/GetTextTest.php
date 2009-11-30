<?php
/**
 * GetTextTest.php for BaseZF in tests/BaseZF/Service
 *
 * @category   BaseZF
 * @package    BaseZF_UnitTest
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

require_once dirname(__FILE__) . '/../../TestHelper.php';

/**
 * Test class for Example
 *
 * @group BaseZF
 * @group BaseZF_Service
 * @group BaseZF_Service_GetText
 */
class BaseZF_Service_GetTextTest extends PHPUnit_Framework_TestCase
{
    protected $_tmpFiles = array();

    protected $_tmpDirs = array();

    protected $_config = array();

    /**
     * Call before all test and on class test loading
     */
    public function setUp()
    {
        // src files
        $srcFileSimple = $this->_getTmpFile('toto.php', '/src', '<?php echo _("I love french girls."); ?>');
        $srcFilePlural = $this->_getTmpFile('titi.php', '/src', '<?php echo sprintf(ngettext("I whant %d donut", "I whant %d donuts", 1), 1); ?>');
        $srcPath =  dirname($srcFileSimple);

        // locale path and files
        $localeDirPath = dirname($this->_getTmpFile('README', '/locale'));
        $potDirPath = dirname($this->_getTmpFile('message.pot', '/locale/dist', ''));

        // add some language files
        $this->_getTmpFile('message.po', '/locale/fr_FR/LC_MESSAGES');
        $this->_getTmpFile('message.po', '/locale/en_GB/LC_MESSAGES');
        $this->_getTmpFile('message.mo', '/locale/fr_FR/LC_MESSAGES');
        $this->_getTmpFile('message.mo', '/locale/en_GB/LC_MESSAGES');

        // set service config
        $this->_config = array(

            // domain path
            'domains' => array(
                'message'   => array($srcPath),
            ),

            // locales gettext env
            'localeDirPath'     => $localeDirPath,
            'potDirPath'        => $potDirPath,
            'poDir'             => 'LC_MESSAGES',

            // bin path
            'xgettextPath'  => 'xgettext',
            'msguniqPath'   => 'msguniq',
            'msgmergePath'  => 'msgmerge',
            'msginitPath'   => 'msginit',
            'msguniqPath'   => 'msguniq',
            'msgfmtPath'    => 'msgfmt',

        );
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
        static $testTmpDir;

        // create unique path for this test
        if (!isset($testTmpDir)) {
            $testTmpDir = 'phpUnitTest-' . __CLASS__ . '-' . microtime(true);
        }

        // sanitize $filePath value to use DIRECTORY_SEPARATOR
        $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

        // create paths
        $tmpDir = sys_get_temp_dir(); // your /tmp path value
        $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . $testTmpDir . $filePath . DIRECTORY_SEPARATOR . $fileName;
        $tmpFileDir = dirname($tmpFile);

        $this->_tmpFiles[$tmpFile] = $tmpFile;

        if (!is_dir($tmpFileDir) && $tmpFileDir != $tmpDir) {

            $tmpDirs = explode(DIRECTORY_SEPARATOR, $tmpFileDir);
            $nbTmpDirs = count($tmpDirs);
            for ($i = 2; $i <= $nbTmpDirs; $i++) {

                $tmpDir = implode(DIRECTORY_SEPARATOR, array_slice($tmpDirs, 0, $i));

                if (!is_dir($tmpDir)) {
                    mkdir($tmpDir);
                    $this->_tmpDirs[] = $tmpDir;
                }
            }
        }

        // add contennt on tmp file
        if (!is_null($fileData)) {
            file_put_contents($tmpFile, $fileData);
        }

        return $tmpFile;
    }


    public function testUpdatePotFiles()
    {
        $gettextService = new BaseZF_Service_GetText($this->_config);

        // create the POT file
        $gettextService->updatePotFiles();

        //readfile($this->_config['potDirPath'] . '/message.pot');

    }

    public function testUpdatePoFiles()
    {
        $gettextService = new BaseZF_Service_GetText($this->_config);

        // create the POT file
        $gettextService->updatePotFiles();

        // create PO files
        $poDomainFilePaths = $gettextService->updatePoFiles(array('fr_FR', 'en_GB'));

        foreach ($poDomainFilePaths as $poDomainFilePath) {
            //readfile($poDomainFilePath);
        }
    }

    public function testDeployMoFiles()
    {
        $gettextService = new BaseZF_Service_GetText($this->_config);

        // create the POT file
        $gettextService->updatePotFiles();

        // create PO files
        $poDomainFilePaths = $gettextService->updatePoFiles(array('fr_FR', 'en_GB'));

        // create MO files
        $moDomainFilePaths = $gettextService->deployMoFiles(array('fr_FR', 'en_GB'), array('message'));

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

        // remove dir
        $tmpDirs = array_reverse($this->_tmpDirs);
        foreach ($tmpDirs as $tmpDir) {
            if (is_dir($tmpDir)) {
                rmdir($tmpDir);
            }
        }

        // reset
        $this->_tmpFiles = array();
        $this->_tmpDirs = array();
    }
}
