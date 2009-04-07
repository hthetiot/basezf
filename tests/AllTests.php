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

        $suite->addTest(BaseZF_AllTests::suite());
        $suite->addTest(MyProject_AllTests::suite());

        return $suite;
    }
}


