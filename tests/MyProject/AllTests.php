<?php
/**
 * AllTests.php for MyProject in tests/MyProject/
 *
 * @category   MyProject_UnitTest
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Framework_AllTests::main');
}

// include PhpUnit Library
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class MyProject_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');

        // $suite->addTestSuite('class');
        // ...

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'MyProject_AllTests::main') {
    MyProject_AllTests::main();
}

