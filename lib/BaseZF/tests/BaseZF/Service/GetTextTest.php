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
        $srcFileSimple1 = $this->_getTmpFile('simple1.php', '/src', '<?php echo _("I love french girls."); ?>');
        $srcFileSimple2 = $this->_getTmpFile('simple2.php', '/src', '<?php echo _("I love my developer life."); ?>');
        $srcFilePlural = $this->_getTmpFile('plural.php', '/src', '<?php echo sprintf(ngettext("I whant %d donut", "I whant %d donuts", 1), 1); ?>');
        $srcPath =  dirname($srcFileSimple1);

        // locale path and files
        $localeDirPath = dirname($this->_getTmpFile('README', '/locale'));
        $potDirPath = dirname($this->_getTmpFile('messages.pot', '/locale/dist', ''));

        // add some language files
        $this->_getTmpFile('messages.po', '/locale/fr_FR/LC_MESSAGES');
        $this->_getTmpFile('messages.po', '/locale/en_GB/LC_MESSAGES');
        $this->_getTmpFile('messages.mo', '/locale/fr_FR/LC_MESSAGES');
        $this->_getTmpFile('messages.mo', '/locale/en_GB/LC_MESSAGES');

        // set service config
        $this->_config = array(

            // domain path
            'domainsPaths' => array(
                'messages'   => array($srcPath),
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
        $tmpDir = sys_get_temp_dir();
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

        // check file content
        $potFilePath = $this->_config['potDirPath'] . '/messages.pot';

        // do we have the expected new file
        $this->assertFileExists($potFilePath);

        // the file contain translation template
        $potFileContent = file_get_contents($potFilePath);

        $this->assertContains('msgid "I love french girls."', $potFileContent);
        $this->assertContains('msgid "I whant %d donut"', $potFileContent);
        $this->assertContains('msgid_plural "I whant %d donuts"', $potFileContent);

    }

    public function testUpdatePoFilesFromScratch()
    {
        // expected po files returned by updatePoFiles
        $poLocaleFilePathsByDomainTest = array(
            'fr_FR' => array('messages' => $this->_config['localeDirPath'] . DIRECTORY_SEPARATOR .  'fr_FR' . DIRECTORY_SEPARATOR . $this->_config['poDir'] . DIRECTORY_SEPARATOR . 'messages.po'),
            'en_GB' => array('messages' => $this->_config['localeDirPath'] . DIRECTORY_SEPARATOR .  'en_GB' . DIRECTORY_SEPARATOR . $this->_config['poDir'] . DIRECTORY_SEPARATOR . 'messages.po'),
        );

        $gettextService = new BaseZF_Service_GetText($this->_config);

        // create the POT file
        $gettextService->updatePotFiles();

        // create PO files
        $poLocaleFilePathsByDomain = $gettextService->updatePoFiles(array('fr_FR', 'en_GB'));

        // do we have the expected results ?
        $this->assertSame($poLocaleFilePathsByDomain, $poLocaleFilePathsByDomainTest);

        // do we have the expected new files
        foreach ($poLocaleFilePathsByDomain as $locale => $filePathsByDomains) {

            foreach ($filePathsByDomains as $filePathsByDomain) {

                $this->assertFileExists($filePathsByDomain);

                // the file contain translation template
                $poFileContent = file_get_contents($filePathsByDomain);

                $this->assertContains('msgid "I love french girls."', $poFileContent);
                $this->assertContains('msgid "I love my developer life."', $poFileContent);
                $this->assertContains('msgid "I whant %d donut"', $poFileContent);
                $this->assertContains('msgid_plural "I whant %d donuts"', $poFileContent);
            }
        }
    }

    public function testUpdatePoFilesWithNewAndRemovedAndUpdatedTranslations()
    {
        // expected po files returned by updatePoFiles
        $poLocaleFilePathsByDomainTest = array(
            'fr_FR' => array('messages' => $this->_config['localeDirPath'] . DIRECTORY_SEPARATOR .  'fr_FR' . DIRECTORY_SEPARATOR . $this->_config['poDir'] . DIRECTORY_SEPARATOR . 'messages.po'),
            'en_GB' => array('messages' => $this->_config['localeDirPath'] . DIRECTORY_SEPARATOR .  'en_GB' . DIRECTORY_SEPARATOR . $this->_config['poDir'] . DIRECTORY_SEPARATOR . 'messages.po'),
        );

        $gettextService = new BaseZF_Service_GetText($this->_config);

        // create the POT file
        $gettextService->updatePotFiles();

        // create PO files
        $poLocaleFilePathsByDomain = $gettextService->updatePoFiles(array('fr_FR', 'en_GB'));

        // do we have the expected results ?
        $this->assertSame($poLocaleFilePathsByDomain, $poLocaleFilePathsByDomainTest);

        // do we have the expected new files
        foreach ($poLocaleFilePathsByDomain as $locale => $filePathsByDomains) {

            foreach ($filePathsByDomains as $filePathsByDomain) {

                $this->assertFileExists($filePathsByDomain);

                // the file contain translation template
                $poFileContent = file_get_contents($filePathsByDomain);

                $this->assertContains('msgid "I love french girls."', $poFileContent);
                $this->assertContains('msgid "I love my developer life."', $poFileContent);
                $this->assertContains('msgid "I whant %d donut"', $poFileContent);
                $this->assertContains('msgid_plural "I whant %d donuts"', $poFileContent);

                // simulate translation for auto fuzzy checking
                file_put_contents($filePathsByDomain, str_replace('msgstr ""', 'msgstr "fuzzy checking"', $poFileContent));

                // the file contain translation template
                $poFileContent = file_get_contents($filePathsByDomain);
            }
        }

        // add a new transaltion and update another
        $this->_getTmpFile('simple1.php', '/src', '<?php echo _("I love french boys."); ?>'); // update
        $this->_getTmpFile('simple2.php', '/src', '<?php  ?>'); // remove
        $this->_getTmpFile('simple3.php', '/src', '<?php echo _("I like Gettext it rocks !"); ?>'); // new

        // create the POT file include new tutu.php file
        $gettextService->updatePotFiles();

        // check file content
        $potFilePath = $this->_config['potDirPath'] . '/messages.pot';

        // the file contain translation template
        $potFileContent = file_get_contents($potFilePath);

        // create PO files include new tutu.php file from pot
        $poLocaleFilePathsByDomain = $gettextService->updatePoFiles(array('fr_FR', 'en_GB'));

        // do we have the expected file
        foreach ($poLocaleFilePathsByDomain as $locale => $filePathsByDomains) {

            foreach ($filePathsByDomains as $filePathsByDomain) {

                $this->assertFileExists($filePathsByDomain);

                // the file contain translation template
                $poFileContent = file_get_contents($filePathsByDomain);

                // check auto fuzzy (simple1.php)
                $this->assertContains('msgid "I love french boys."', $poFileContent);
                $this->assertContains('#| msgid "I love french girls."', $poFileContent);

                // check removed (simple2.php)
                $this->assertContains('#~ msgid "I love my developer life."', $poFileContent);

                // check new translation (simple3.php)
                $this->assertContains('msgid "I like Gettext it rocks !"', $poFileContent);

                // check existing (plural.php)
                $this->assertContains('msgid "I whant %d donut"', $poFileContent);
                $this->assertContains('msgid_plural "I whant %d donuts"', $poFileContent);
            }
        }
    }

    public function testDeployMoFiles()
    {
        // expected po files returned by updatePoFiles
        $poLocaleFilePathsByDomainTest = array(
            'fr_FR' => array('messages' => $this->_config['localeDirPath'] . DIRECTORY_SEPARATOR .  'fr_FR' . DIRECTORY_SEPARATOR . $this->_config['poDir'] . DIRECTORY_SEPARATOR . 'messages.po'),
            'en_GB' => array('messages' => $this->_config['localeDirPath'] . DIRECTORY_SEPARATOR .  'en_GB' . DIRECTORY_SEPARATOR . $this->_config['poDir'] . DIRECTORY_SEPARATOR . 'messages.po'),
        );

        // expected mo files returned by deployMoFiles
        $moLocaleFilePathsByDomainTest = array(
            'fr_FR' => array('messages' => $this->_config['localeDirPath'] . DIRECTORY_SEPARATOR .  'fr_FR' . DIRECTORY_SEPARATOR . $this->_config['poDir'] . DIRECTORY_SEPARATOR . 'messages.mo'),
            'en_GB' => array('messages' => $this->_config['localeDirPath'] . DIRECTORY_SEPARATOR .  'en_GB' . DIRECTORY_SEPARATOR . $this->_config['poDir'] . DIRECTORY_SEPARATOR . 'messages.mo'),
        );

        $gettextService = new BaseZF_Service_GetText($this->_config);

        // create the POT file
        $gettextService->updatePotFiles();

        // create PO files
        $poLocaleFilePathsByDomain = $gettextService->updatePoFiles(array('fr_FR', 'en_GB'));

        // create MO files
        $moLocaleFilePathsByDomain = $gettextService->deployMoFiles(array('fr_FR', 'en_GB'));

        // do we have the expected results ?
        $this->assertSame($moLocaleFilePathsByDomain, $moLocaleFilePathsByDomainTest);
        $this->assertSame($poLocaleFilePathsByDomain, $poLocaleFilePathsByDomainTest);
    }

    public function testTranslateFromArrayAndDeployPoFile()
    {
        /*
        // @todo plural....
        // @todo manage error msgs
        // àtodo manage encoding
        $translations = array(
            'I love french girls.' => "J'aime les femmes Française",
            'I whant %d donut'     => array(
                0 => 'Je veux %d donut',
                1 => 'Je veux %d donuts',
            ),
        );
        */

        $translations = array(
            'I love french girls.' => "J'aime les femmes Française",
            'I whant %d donut'     => 'Je veux %d donut',
        );

        // init config
        $gettextService = new BaseZF_Service_GetText($this->_config);

        // create the POT file
        $gettextService->updatePotFiles();

        // create PO files
        $poLocaleFilePathsByDomain = $gettextService->updatePoFiles(array('fr_FR'));

        $poToTranslate = $poLocaleFilePathsByDomain['fr_FR']['messages'];

        // process translation from array has fuzzy values
        $translator = new BaseZF_Service_GetText_Translator_Array($poToTranslate);
        $translator->translate($translations);
        $translator->saveFile();

        // create MO files with fuzzy values
        $toto = $gettextService->deployMoFiles(array('fr_FR'), array('messages'), true);
        $gettextService->iniTranslation('fr_FR', array('messages'));

        // check translation
        foreach ($translations as $original => $translated) {
            $this->assertSame($translated,_($original));
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
