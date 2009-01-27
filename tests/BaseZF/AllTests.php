<?php
/**
 * AllTests.php for BaseZF in tests/BaseZF/
 *
 * @category   BaseZF_UnitTest
 * @package    BaseZF
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thétiot (hthetiot)
 */

// include PhpUnit Library
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class BaseZF_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('BaseZF Framework');

        // $suite->addTestSuite('BaseZF_DbItemTest');
        // ...

        return $suite;
    }
}

