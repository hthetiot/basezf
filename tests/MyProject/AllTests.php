<?php
/**
 * AllTests.php for MyProject in tests/MyProject/
 *
 * @category   MyProject_UnitTest
 * @package    MyProject
 * @copyright  Copyright (c) 2008 MyProject
 * @author     Harold Thétiot (hthetiot)
 */

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

        $suite->addTestSuite('MyProject_ExampleTest');
        // ...

        return $suite;
    }
}

