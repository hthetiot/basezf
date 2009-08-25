<?php
/**
 * ExampleTest.php for BaseZF in tests/
 *
 * @category   BaseZF
 * @package    BaseZF_UnitTest
 * @copyright  Copyright (c) 2008 BaseZF
 * @author     Harold Thetiot (hthetiot)
 */

// Load PhpUnit Libs
require_once 'PHPUnit/Framework.php';


class BaseZF_ExampleTest extends PHPUnit_Framework_TestCase
{
    protected $_example = null;

    /**
     * Call before all test and on class test loading
     */
    public function setUp()
    {
        // configure test here
    }

    public function testUpdatePropertyValue()
    {
        // use time to have floating value
        $value = time();

        // compare waiting results with results
        $this->assertEquals($value, $value);
    }

    /**
     * Call after all test and on class test loading
     */
    public function tearDown()
    {
        // clean database or test generated data for example
    }
}

