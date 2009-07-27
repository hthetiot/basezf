<?php
/**
 * AllTests.php for BaseZF in tests/
 *
 * @category   BaseZF_UnitTest
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

// PhpUnit Libs
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

// Set current environment config mode to test
define('CONFIG_ENV', 'test');

// Init Environement
require_once '../includes/auto_prepend.php';

class AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');

        // get all AllTests.php file in subdirectories and add them as Test suite
        $allTestsSuites = glob(dirname(__FILE__) . '/*/AllTests.php');
        foreach ($allTestsSuites as $allTestsSuite) {
            $allTestsClassName = basename(dirname($allTestsSuite)) . '_' . basename($allTestsSuite, '.php');
            $suite->addTest(call_user_func($allTestsClassName . '::suite'));
        }

        return $suite;
    }
}


