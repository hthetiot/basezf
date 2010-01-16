<?php
/**
 * GetTextTest.php for BaseZF in tests/BaseZF/Service
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
 * Test class for Example
 *
 * @group BaseZF
 * @group BaseZF_Service
 * @group BaseZF_Service_GetText
 */
class BaseZF_UnitTest_Service_GetTextTest extends PHPUnit_Framework_TestCase
{
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
        BaseZF_UnitTest_TemporaryFile::setNameSpace(__CLASS__);

        $tmpFilePath = BaseZF_UnitTest_TemporaryFile::getFile($fileName, $filePath, $fileData);

        return $tmpFilePath;
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
        // @todo manage encoding
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

        $this->markTestSkipped('Still trying to determine a scenario to test plural');
    }

    public function testMergePoFile()
    {
        //@todo
        $this->markTestSkipped('Still trying to determine a scenario to test this');
    }

    public function testUpdateMsgIdFromPoFile()
    {
        //@todo
        $this->markTestSkipped('Still trying to determine a scenario to test this');
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

